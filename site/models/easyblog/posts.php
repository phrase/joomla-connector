<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.1.0
 */
class MemsourceConnectorModelsEasyblogPosts extends MemsourceConnectorModelsContent
{
	const DB_TABLE = '#__easyblog_post';
	const DB_TABLE_ASSOCIATIONS = '#__easyblog_associations';
	const DB_TABLE_REVISIONS = '#__easyblog_revisions';

	/**
	 * Are EasyBlog posts available?
	 *
	 * @return  boolean
	 *
	 * @since   1.1.0
	 */
	public static function isExtensionActive()
	{
		return class_exists('EB') && method_exists('EB', 'post');
	}

	/**
	 * Get posts stored in source language.
	 *
	 * @return  MemsourceConnectorModelsDtoItem[]
	 *
	 * @since   1.1.0
	 */
	public function getItemsInSourceLang()
	{
		$lang = JFactory::getLanguage()->getDefault();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('id', 'title', 'UNIX_TIMESTAMP(modified) AS modified', 'CHAR_LENGTH(CONCAT(title, intro)) AS size'))
			->from(self::DB_TABLE)
			->where('language = ' . $db->quote($lang));
		$db->setQuery($query);

		return $db->loadObjectList('', MemsourceConnectorModelsDtoItem::class);
	}

	/**
	 * Get post detail with content.
	 *
	 * @param   int $id Post ID
	 *
	 * @return  MemsourceConnectorModelsDtoDetail
	 *
	 * @since   1.1.0
	 */
	public function getItemDetail($id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'document', 'intro')))
			->from(self::DB_TABLE)
			->where('id = ' . $db->quote($id));
		$db->setQuery($query);

		$post = $db->loadObject();

		$html = '';
		$this->convertBlocksToHtml(
			json_decode($post->document)->blocks,
			$html
		);

		if ((isset($_GET['raw']) && $_GET['raw'] === '1') || (isset($_POST['raw']) && $_POST['raw'] === '1'))
		{
			echo "easyblog post";
			echo "\n-> id: " . $post->id;
			echo "\n-> title: " . $post->title;
			echo "\n-> intro:\n";
			echo $post->intro;
			echo "\n-> html:\n";
			echo $html;
			echo "\n--------------\n";
		}

		$response = new MemsourceConnectorModelsDtoDetail;
		$response->setId($post->id);
		$response->setTitle($post->title);
		$response->setBody($html);

		return $response;
	}

	/**
	 * Store translated post.
	 *
	 * @param   int    $id      Post ID
	 * @param   string $lang    Target language
	 * @param   string $title   Translated post title
	 * @param   string $content Translated post (without title)
	 *
	 * @return  MemsourceConnectorModelsDtoDetail
	 *
	 * @since   1.1.0
	 */
	public function storeTranslation($id, $lang, $title, $content)
	{
		$lang = MemsourceConnectorHelpersLanguage::denormalizeLangCode($lang);
		$associatedPostId = $this->getAssociatedPostId($id, $lang);

		if ($associatedPostId === null)
		{
			$post = $this->createAssociatedPost($id, $lang);
		}
		else
		{
			$post = $this->getPostById($associatedPostId);
		}

		$post->title = $title;
		$post->modified = date('Y-m-d H:i:s');
		$post->intro = '';

		$blocks = $this->htmlToBlocks($content);
		$post->document = $this->updateBlocksInDocument($post->document, $blocks);

		$document = EB::document($post->document);
		$post->intro = $document->processContent();
		$post->content = $document->processIntro();

		$db = JFactory::getDbo();

		$db->transactionStart();

		if ($associatedPostId === null)
		{
			$db->insertObject(self::DB_TABLE, $post);
			$associatedPostId = $this->storeAssociations($id, $post);
			$this->createUniqueAlias($associatedPostId, $title, $post->category_id);
		}
		else
		{
			$db->updateObject(self::DB_TABLE, $post, array('id'));
			$this->deletePostRevisions($post->id);
		}

		$db->transactionCommit();

		return $this->getItemDetail($associatedPostId);
	}

	/**
	 * Parse JSON from post->document and create an HTML for translation.
	 *
	 * @param   stdClass $blocks Encoded #_easyblog_post.document
	 * @param   string   $html   Result of conversion
	 *
	 * @return void
	 *
	 * @since  1.1.0
	 */
	private function convertBlocksToHtml($blocks, &$html)
	{
		foreach ($blocks as $block)
		{
			$html .= '<span data-memsource-block-uid="' . $block->uid . '"></span>';
			$html .= $block->editableHtml;
			$html .= '<span class="memsource-block-divider"></span>';

			$this->convertBlocksToHtml($block->blocks, $html);
		}
	}

	/**
	 * Update converted blocks.
	 *
	 * @param   string $document         EB post->document
	 * @param   array  $translatedBlocks Array of translations
	 *
	 * @return  string
	 *
	 * @since   1.1.0
	 */
	private function updateBlocksInDocument($document, $translatedBlocks)
	{
		$decoded = json_decode($document);
		$this->convertHtmlToBlocks($decoded->blocks, $translatedBlocks);

		return json_encode($decoded);
	}

	/**
	 * Convert blocks using translations.
	 *
	 * @param   array $blocks           Array of blocks
	 * @param   array $translatedBlocks Array of translations
	 *
	 * @return void
	 *
	 * @since   1.1.0
	 */
	private function convertHtmlToBlocks(&$blocks, &$translatedBlocks)
	{
		foreach ($blocks as &$block)
		{
			if (isset($translatedBlocks[$block->uid]))
			{
				$block->editableHtml = $translatedBlocks[$block->uid];
				unset($block->text);
				$block->html = $block->editableHtml;
			}

			$this->convertHtmlToBlocks($block->blocks, $translatedBlocks);
		}
	}

	/**
	 * Convert HTML to array of blocks.
	 *
	 * @param   string $document EB post->document
	 *
	 * @return  array
	 *
	 * @since   1.1.0
	 */
	private function htmlToBlocks($document)
	{
		$result = array();
		$strings = explode('<span class="memsource-block-divider"></span>', $document);

		foreach ($strings as $string)
		{
			$matchResult = preg_match('|<span data-memsource-block-uid="([0-9]+)"></span>(.*)|sm', $string, $matches);

			if ($matchResult > 0)
			{
				$result[$matches[1]] = $matches[2];
			}
		}

		return $result;
	}

	/**
	 * Create alias using stringURLSafe function. Checks that there is no duplicate alias in given category.
	 *
	 * @param   int    $id         Post ID.
	 * @param   string $title      Post title.
	 * @param   int    $categoryId Post category.
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	private function createUniqueAlias($id, $title, $categoryId)
	{
		$db = JFactory::getDbo();
		$alias = JFilterOutput::stringURLSafe($title);

		$query = $db->getQuery(true);
		$query->select($db->quoteName('permalink'))
			->from(self::DB_TABLE)
			->where($db->quoteName('permalink') . ' = ' . $db->quote($alias))
			->where('category_id = ' . $db->quote($categoryId));
		$db->setQuery($query);

		if ($db->loadResult() !== null)
		{
			$alias .= "-$id";
		}

		$query = "update `" . self::DB_TABLE . "`";
		$query .= " set `permalink` = " . $db->quote($alias);
		$query .= " where `id` = " . $db->Quote($id);

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Find translated post in database, return null if post not found.
	 *
	 * @param   int    $id   ID of source post.
	 * @param   string $lang Language code (in Joomla format) of target post.
	 *
	 * @return  string|void
	 *
	 * @since   1.1.0
	 */
	private function getAssociatedPostId($id, $lang)
	{
		$db = JFactory::getDbo();

		$query = "select a.`post_id`";
		$query .= " from `" . self::DB_TABLE_ASSOCIATIONS . "` as a";
		$query .= " inner join `" . self::DB_TABLE_ASSOCIATIONS . "` as b on a.`key` = b.`key`";
		$query .= " inner join `" . self::DB_TABLE . "` as p on a.`post_id` = p.`id`";
		$query .= " where b.`post_id` = " . $db->Quote($id);
		$query .= " and p.`language` = " . $db->Quote($lang);
		$query .= " limit 1";

		$db->setQuery($query);
		$result = $db->loadObject();

		if ($result !== null)
		{
			return $result->post_id;
		}
	}

	/**
	 * Create in-memory copy of source post, set language code, clear id, version and createion date.
	 *
	 * @param   int    $id   ID of source post.
	 * @param   string $lang Code of target language.
	 *
	 * @return  stdClass
	 *
	 * @since   1.1.0
	 */
	private function createAssociatedPost($id, $lang)
	{
		$post = $this->getPostById($id);

		$post->id = null;
		$post->created = date('Y-m-d H:i:s');
		$post->language = $lang;
		$post->revision_id = null;
		$post->autoposting = null;

		return $post;
	}

	/**
	 * Get raw post from database.
	 *
	 * @param   int $id post ID.
	 *
	 * @return  stdClass
	 *
	 * @since   1.1.0
	 */
	private function getPostById($id)
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('*')
			->from(self::DB_TABLE)
			->where('id = ' . $db->quote($id));

		$db->setQuery($query);

		return $db->loadObject();
	}

	/**
	 * Delete all revisions of a post.
	 *
	 * @param   int $postId EB post ID
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	private function deletePostRevisions($postId)
	{
		$db = JFactory::getDbo();

		$query = "delete from `" . self::DB_TABLE_REVISIONS . "`";
		$query .= " where `post_id` = " . $db->Quote($postId);

		$db->setQuery($query);
		$db->execute();
	}

	/**
	 * Pair source post with target using multilanguage associations.
	 *
	 * @param   int      $sourcePostId Source post ID
	 * @param   stdClass $targetPost   Stored post (will be used for ID lookup).
	 *
	 * @return  integer
	 *
	 * @since   1.1.0
	 */
	private function storeAssociations($sourcePostId, $targetPost)
	{
		$db = JFactory::getDbo();

		// 1. load already paired posts

		$query = "select a.`post_id`, a.`key`, p.`language`, p.`title`";
		$query .= " from `" . self::DB_TABLE_ASSOCIATIONS . "` as a";
		$query .= " inner join `" . self::DB_TABLE_ASSOCIATIONS . "` as b on a.`key` = b.`key`";
		$query .= " inner join `" . self::DB_TABLE . "` as p on a.`post_id` = p.`id`";
		$query .= " where b.`post_id` = " . $db->Quote($sourcePostId);

		$db->setQuery($query);

		$associations = $db->loadObjectList();

		if (empty($associations))
		{
			$sourcePost = $this->getPostById($sourcePostId);
			$obj = new stdClass;
			$obj->language = $sourcePost->language;
			$obj->post_id = $sourcePost->id;
			$obj->title = $sourcePost->title;
			$associations[] = $obj;
		}

		// 2. add targetArticle->id to associations (if needed)

		if ($targetPost->id === null)
		{
			$query = $db->getQuery(true);
			$query->select('id')
				->from(self::DB_TABLE)
				->where('permalink = ' . $db->quote($targetPost->permalink))
				->where('category_id = ' . $db->quote($targetPost->category_id))
				->where('modified = ' . $db->quote($targetPost->modified))
				->where('language = ' . $db->quote($targetPost->language))
				->order('id DESC');
			$db->setQuery($query);
			$targetPost->id = $db->loadObject()->id;

			$obj = new stdClass;
			$obj->language = $targetPost->language;
			$obj->post_id = $targetPost->id;
			$obj->title = $targetPost->title;
			$associations[] = $obj;
		}

		// 3. delete all associations in DB

		$query = "delete a from `" . self::DB_TABLE_ASSOCIATIONS . "` as a";
		$query .= " inner join `" . self::DB_TABLE_ASSOCIATIONS . "` as b on a.`key` = b.`key`";
		$query .= " where b.`post_id` = " . $db->Quote($sourcePostId);

		$db->setQuery($query);
		$db->execute();

		// 4. insert updated associations into DB

		$associationKeyData = array();

		foreach ($associations as $association)
		{
			$obj = new stdClass;
			$obj->code = $association->language;
			$obj->id = $association->post_id;
			$obj->post = $association->title;
			$associationKeyData[] = $obj;
		}

		$associationKey = md5(json_encode($associationKeyData));

		foreach ($associations as $association)
		{
			$this->storeSingleAssociation($association->post_id, $associationKey);
		}

		return $targetPost->id;
	}

	/**
	 * @param   int    $id  Post ID
	 * @param   string $key Associoation key.
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	private function storeSingleAssociation($id, $key)
	{
		$association = (object) array('post_id' => $id, 'key' => $key);
		$db = JFactory::getDbo();
		$db->insertObject(self::DB_TABLE_ASSOCIATIONS, $association);
	}
}
