<?php
/**
 * @copyright Copyright (C) 2019 Phrase a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
abstract class MemsourceConnectorApiContent extends MemsourceConnectorApiBase
{
	/**
	 * Get list of categories.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getList()
	{
		return array('items' => $this->getModel()->getItemsInSourceLang());
	}

	/**
	 * Get category detail.
	 *
	 * @param   int $id ID.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getDetail($id)
	{
		return array('item' => $this->getModel()->getItemDetail($id));
	}

	/**
	 * Store translated category.
	 *
	 * @param   int    $id      Category ID.
	 * @param   string $lang    Target language.
	 * @param   string $title   Translated category name.
	 * @param   string $content Translated category description (without title).
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function storeTranslation($id, $lang, $title, $content)
	{
		return array('item' => $this->getModel()->storeTranslation($id, $lang, $title, $content));
	}

	/**
	 * Get instance of the model class.
	 *
	 * @return  MemsourceConnectorModelsContent
	 *
	 * @since   1.0.0
	 */
	abstract protected function getModel();
}
