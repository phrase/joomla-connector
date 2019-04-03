<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('ContentAssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/associations.php');
JLoader::register('ContentModelArticle', JPATH_ADMINISTRATOR . '/components/com_content/models/article.php');
JLoader::register('ContentTableFeatured', JPATH_ADMINISTRATOR . '/components/com_content/tables/featured.php');

/**
 * @since 1.0.0
 */
class MemsourceConnectorModelsArticles extends MemsourceConnectorModelsContent
{
	const DB_TABLE = '#__content';

	/**
	 * Get articles stored in source language.
	 *
	 * @return  MemsourceConnectorModelsDtoItem[]
	 *
	 * @since   1.0.0
	 */
	public function getItemsInSourceLang()
	{
		$lang = JFactory::getLanguage()->getDefault();

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select(array('id', 'title', 'UNIX_TIMESTAMP(modified) AS modified', 'CHAR_LENGTH(CONCAT(title, introtext)) AS size'))
			->from(self::DB_TABLE)
			->where('language = ' . $db->quote($lang));
		$db->setQuery($query);

		return $db->loadObjectList('', MemsourceConnectorModelsDtoItem::class);
	}

	/**
	 * Get article detail with content.
	 *
	 * @param   int $id Article ID.
	 *
	 * @return  MemsourceConnectorModelsDtoDetail
	 *
	 * @since   1.0.0
	 */
	public function getItemDetail($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'introtext'), array(null, null, 'body')))
			->from(self::DB_TABLE)
			->where('id = ' . $db->quote($id));
		$db->setQuery($query);

		return $db->loadObject(MemsourceConnectorModelsDtoDetail::class);
	}

	/**
	 * Store translated article.
	 *
	 * @param   int    $id      Article ID.
	 * @param   string $lang    Target language.
	 * @param   string $title   Translated article title.
	 * @param   string $content Translated article (without title).
	 *
	 * @return  MemsourceConnectorModelsDtoDetail
	 *
	 * @since   1.0.0
	 */
	public function storeTranslation($id, $lang, $title, $content)
	{
		$lang = MemsourceConnectorHelpersLanguage::denormalizeLangCode($lang);
		$associatedArticleId = $this->getAssociatedArticleId($id, $lang);

		if ($associatedArticleId === null)
		{
			$article = $this->createAssociatedArticle($id, $lang);
		}
		else
		{
			$article = $this->getArticleById($associatedArticleId);
			$article->version++;
		}

		$article->title = $title;
		$article->introtext = $content;
		$article->modified = date('Y-m-d H:i:s');

		$db = JFactory::getDbo();

		if ($associatedArticleId === null)
		{
			$db->transactionStart();
			$db->insertObject(self::DB_TABLE, $article);
			$associatedArticleId = $this->storeAssociations($id, $article);
			$this->createUniqueAlias($associatedArticleId, $title, $article->catid);
			$this->refreshArticle($associatedArticleId);
			$db->transactionCommit();
		}
		else
		{
			$db->updateObject(self::DB_TABLE, $article, array('id'));
		}

		return $this->getItemDetail($associatedArticleId);
	}

	/**
	 * Create alias using stringURLSafe function. Checks that there is no duplicate alias in given category.
	 *
	 * @param   int    $id         Article ID.
	 * @param   string $title      Article title.
	 * @param   int    $categoryId Article category.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function createUniqueAlias($id, $title, $categoryId)
	{
		$alias = JFilterOutput::stringURLSafe($title);

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName('alias'))
			->from(self::DB_TABLE)
			->where($db->quoteName('alias') . ' = ' . $db->quote($alias))
			->where('catid = ' . $db->quote($categoryId));
		$db->setQuery($query);

		if ($db->loadResult() !== null)
		{
			$alias .= "-$id";
		}

		$update = (object) array('id' => $id, 'alias' => $db->quote($alias));
		$db->updateObject(self::DB_TABLE, $update, array('id'));
	}

	/**
	 * Find translated article in database, return null if article not found.
	 *
	 * @param   int    $id   ID of source article.
	 * @param   string $lang Language code (in Joomla format) of target article.
	 *
	 * @return  string|void
	 *
	 * @since   1.0.0
	 */
	private function getAssociatedArticleId($id, $lang)
	{
		$helper = new ContentAssociationsHelper;
		$associations = $helper->getAssociations('article', $id);

		foreach ($associations as $association)
		{
			if ($association->language === $lang)
			{
				return strtok($association->id, ':');
			}
		}
	}

	/**
	 * Create in-memory copy of source article, set language code, clear id, version and createion date.
	 *
	 * @param   int    $id   ID of source article.
	 * @param   string $lang Code of target language.
	 *
	 * @return  stdClass
	 *
	 * @since   1.0.0
	 */
	private function createAssociatedArticle($id, $lang)
	{
		$article = $this->getArticleById($id);

		$article->id = null;
		$article->asset_id = null;
		$article->created = date('Y-m-d H:i:s');
		$article->version = 1;
		$article->language = $lang;

		$targetCategoryId = $this->getAssociatedCategory($article->catid, $lang);

		if ($targetCategoryId !== null)
		{
			$article->catid = $targetCategoryId;
		}

		return $article;
	}

	/**
	 * Get raw article from database.
	 *
	 * @param   int $id Article ID.
	 *
	 * @return  stdClass
	 *
	 * @since   1.0.0
	 */
	private function getArticleById($id)
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
	 * Find associated category in database.
	 *
	 * @param   int    $id   Source category ID.
	 * @param   string $lang Target category language code.
	 *
	 * @return  string|void
	 *
	 * @since   1.0.0
	 */
	private function getAssociatedCategory($id, $lang)
	{
		$helper = new ContentAssociationsHelper;
		$associations = $helper->getAssociations('category', $id);

		foreach ($associations as $association)
		{
			if ($association->language === $lang)
			{
				return strtok($association->id, ':');
			}
		}
	}

	/**
	 * Pair source article with target using multilanguage associations.
	 *
	 * @param   int      $sourceArticleId Source article ID
	 * @param   stdClass $targetArticle   Stored article (will be used for ID lookup).
	 *
	 * @return  integer
	 *
	 * @since   1.0.0
	 */
	private function storeAssociations($sourceArticleId, $targetArticle)
	{
		$db = JFactory::getDbo();

		if ($targetArticle->id === null)
		{
			$query = $db->getQuery(true);
			$query->select('id')
				->from(self::DB_TABLE)
				->where('alias = ' . $db->quote($targetArticle->alias))
				->where('catid = ' . $db->quote($targetArticle->catid))
				->where('modified = ' . $db->quote($targetArticle->modified))
				->where('language = ' . $db->quote($targetArticle->language))
				->order('id DESC');
			$db->setQuery($query);
			$targetArticle->id = $db->loadObject()->id;
		}

		$query = $db->getQuery(true);
		$query->select($db->quoteName('key'))
			->from('#__associations')
			->where("context = 'com_content.item'")
			->where('id = ' . $db->quote($sourceArticleId));
		$db->setQuery($query);
		$associationKey = $db->loadResult();

		if ($associationKey === null)
		{
			$associationKey = md5("com_content.item-translation-$sourceArticleId");
			$this->storeSingleAssociation($sourceArticleId, $associationKey);
		}

		$this->storeSingleAssociation($targetArticle->id, $associationKey);

		return $targetArticle->id;
	}

	/**
	 * Refresh article in DB (create record in #__assets table).
	 *
	 * @param   int $articleId Article ID.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function refreshArticle($articleId)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')->from(self::DB_TABLE)->where("id = $articleId");
		$update_data = $db->setQuery($query)->loadAssoc();
		/** @var ContentModelArticle $articleModel */
		$articleModel = JModelLegacy::getInstance('Article', 'ContentModel');
		$articleModel->save($update_data);
	}

	/**
	 * @param   int    $id  Article ID
	 * @param   string $key Associoation key.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function storeSingleAssociation($id, $key)
	{
		$association = (object) array('context' => 'com_content.item', 'id' => $id, 'key' => $key);
		$db = JFactory::getDbo();
		$db->insertObject('#__associations', $association);
	}
}
