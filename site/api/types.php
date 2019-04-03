<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
class MemsourceConnectorApiTypes extends MemsourceConnectorApiBase
{
	/**
	 * Get list of translatable content types.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getList()
	{
		return array(
			'types' => array(
				'articles',
				'categories'
			)
		);
	}
}
