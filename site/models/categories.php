<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('ContentAssociationsHelper', JPATH_ADMINISTRATOR . '/components/com_content/helpers/associations.php');
JLoader::register('CategoriesModelCategory', JPATH_SITE . '/components/com_categories/models/categories.php');

/**
 * @since 1.0.0
 */
class MemsourceConnectorModelsCategories extends MemsourceConnectorModelsContent
{
	const DB_TABLE = '#__categories';

	/**
	 * Get translatable categories.
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
		$query->select(array('id', 'title', 'UNIX_TIMESTAMP(modified_time) AS modified', 'CHAR_LENGTH(CONCAT(title, description)) AS size'))
			->from(self::DB_TABLE)
			->where('language IN (' . $db->quote($lang) . ", '*')")
			->where("extension = 'com_content'");
		$db->setQuery($query);

		return $db->loadObjectList('', MemsourceConnectorModelsDtoItem::class);
	}

	/**
	 * Get category detail with content.
	 *
	 * @param   int $id Category ID.
	 *
	 * @return  MemsourceConnectorModelsDtoDetail
	 *
	 * @since   1.0.0
	 */
	public function getItemDetail($id)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'description'), array(null, null, 'body')))
			->from(self::DB_TABLE)
			->where("id = '$id'");
		$db->setQuery($query);

		return $db->loadObject(MemsourceConnectorModelsDtoDetail::class);
	}

	/**
	 * Store translated category.
	 *
	 * @param   int    $id      Category ID.
	 * @param   string $lang    Target language.
	 * @param   string $title   Translated category title.
	 * @param   string $content Translated category description (without title).
	 *
	 * @return  MemsourceConnectorModelsDtoDetail
	 *
	 * @since   1.0.0
	 */
	public function storeTranslation($id, $lang, $title, $content)
	{
		$lang = MemsourceConnectorHelpersLanguage::denormalizeLangCode($lang);
		$associatedCategoryId = $this->getAssociatedCategoryId($id, $lang);

		if ($associatedCategoryId === null)
		{
			$category = $this->createAssociatedCategory($id, $lang);
		}
		else
		{
			$category = $this->getCategoryById($associatedCategoryId);
			$category->version++;
		}

		$category->title = $title;
		$category->description = $content;
		$category->modified_time = date('Y-m-d H:i:s');

		$db = JFactory::getDbo();

		if ($associatedCategoryId === null)
		{
			$db->transactionStart();
			$category->store();
			$associatedCategoryId = $this->storeAssociations($id, $category);
			$this->createUniqueAlias($associatedCategoryId, $title, $lang, $category->parent_id);
			$db->transactionCommit();
		}
		else
		{
			$db->updateObject(self::DB_TABLE, $category, array('id'));
		}

		return $this->getItemDetail($associatedCategoryId);
	}

	/**
	 * Create alias using stringURLSafe function. Checks that there is no duplicate alias.
	 *
	 * @param   int    $id       Category ID.
	 * @param   string $title    Category title.
	 * @param   string $language Language code.
	 * @param   int    $parentId Parent category.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function createUniqueAlias($id, $title, $language, $parentId)
	{
		$alias = JFilterOutput::stringURLSafe($title);

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName('alias'))
			->from(self::DB_TABLE)
			->where($db->quoteName('alias') . ' = ' . $db->quote($alias))
			->where("extension = 'com_content'")
			->where($db->quoteName('language') . ' = ' . $db->quote($language))
			->where($db->quoteName('parent_id') . ' = ' . $db->quote($parentId));
		$db->setQuery($query);

		if ($db->loadResult() !== null)
		{
			$alias .= "-$id";
		}

		$update = (object) array('id' => $id, 'alias' => $alias, 'path' => $alias);
		$db->updateObject(self::DB_TABLE, $update, array('id'));
	}

	/**
	 * Find translated category in database, return null if category not found.
	 *
	 * @param   int    $id   ID of source category.
	 * @param   string $lang Language code (in Joomla format) of target category.
	 *
	 * @return  string|void
	 *
	 * @since   1.0.0
	 */
	private function getAssociatedCategoryId($id, $lang)
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
	 * Create in-memory copy of given catgeory.
	 *
	 * @param   int    $id   Category ID.
	 * @param   string $lang Language code.
	 *
	 * @return  JTableNested
	 *
	 * @since   1.0.0
	 */
	private function createAssociatedCategory($id, $lang)
	{
		$oldCategory = $this->getCategoryById($id);

		/** @var JTableNested $newCategory */
		$newCategory = JTable::getInstance('Category');
		$newCategory->setLocation($newCategory->getRootId(), 'last-child');
		$newCategory->extension = $oldCategory->extension;
		$newCategory->published = $oldCategory->published;
		$newCategory->access = $oldCategory->access;
		$newCategory->params = $oldCategory->params;
		$newCategory->metadata = $oldCategory->metadata;
		$newCategory->language = $lang;
		$newCategory->created_time = date('Y-m-d H:i:s');
		$newCategory->version = 1;

		return $newCategory;
	}

	/**
	 * Find category by ID.
	 *
	 * @param   int $id Category ID.
	 *
	 * @return  JTableNested|stdClass|boolean
	 *
	 * @since   1.0.0
	 */
	private function getCategoryById($id)
	{
		$category = JTable::getInstance('Category');
		$category->load($id);

		return $category;
	}

	/**
	 * Store association between source and target category.
	 *
	 * @param   int      $sourceCategoryId Source category ID.
	 * @param   stdClass $targetCategory   Target category object.
	 *
	 * @return  integer
	 *
	 * @since   1.0.0
	 */
	private function storeAssociations($sourceCategoryId, $targetCategory)
	{
		$db = JFactory::getDbo();

		if ($targetCategory->id === null)
		{
			$query = $db->getQuery(true);
			$query->select('id')
				->from(self::DB_TABLE)
				->where('extension = ' . $db->quote($targetCategory->extension))
				->where('alias = ' . $db->quote($targetCategory->alias))
				->where('modified_time = ' . $db->quote($targetCategory->modified_time))
				->where('language = ' . $db->quote($targetCategory->language))
				->order('id DESC');
			$db->setQuery($query);
			$targetCategory->id = $db->loadObject()->id;
		}

		$query = $db->getQuery(true);
		$query->select($db->quoteName('key'))
			->from('#__associations')
			->where("context = 'com_categories.item'")
			->where('id = ' . $db->quote($sourceCategoryId));
		$db->setQuery($query);
		$associationKey = $db->loadResult();

		if ($associationKey === null)
		{
			$associationKey = md5("com_categories.item-translation-$sourceCategoryId");
			$this->storeSingleAssociation($sourceCategoryId, $associationKey);
		}

		$this->storeSingleAssociation($targetCategory->id, $associationKey);

		return $targetCategory->id;
	}

	/**
	 * @param   int    $id  Category ID
	 * @param   string $key Associoation key.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function storeSingleAssociation($id, $key)
	{
		$association = (object) array('context' => 'com_categories.item', 'id' => $id, 'key' => $key);
		$db = JFactory::getDbo();
		$db->insertObject('#__associations', $association);
	}
}
