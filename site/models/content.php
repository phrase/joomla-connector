<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
abstract class MemsourceConnectorModelsContent extends JModelLegacy
{
	/**
	 * Get translatable items.
	 *
	 * @return  MemsourceConnectorModelsDtoItem[]
	 *
	 * @since   1.0.0
	 */
	abstract public function getItemsInSourceLang();

	/**
	 * Get item detail with content for translation.
	 *
	 * @param   int $id Item ID.
	 *
	 * @return  MemsourceConnectorModelsDtoDetail
	 *
	 * @since   1.0.0
	 */
	abstract public function getItemDetail($id);

	/**
	 * Store translated content.
	 *
	 * @param   int    $id      Item ID.
	 * @param   string $lang    Target language.
	 * @param   string $title   Translated title.
	 * @param   string $content Translated body (without title).
	 *
	 * @return  MemsourceConnectorModelsDtoDetail
	 *
	 * @since   1.0.0
	 */
	abstract public function storeTranslation($id, $lang, $title, $content);
}
