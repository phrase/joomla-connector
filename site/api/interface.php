<?php
/**
 * @copyright Copyright (C) 2019 Phrase a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
interface MemsourceConnectorApiInterface
{
	/**
	 * Get list of given resource.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getList();

	/**
	 * Get single resource by ID.
	 *
	 * @param   int $id ID.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getDetail($id);

	/**
	 * Store translated content.
	 *
	 * @param   int    $id      ID.
	 * @param   string $lang    Target language.
	 * @param   string $title   Translated title.
	 * @param   string $content Translated content.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function storeTranslation($id, $lang, $title, $content);
}
