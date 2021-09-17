<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.1
 */
class MemsourceConnectorApiAccount extends MemsourceConnectorApiBase
{
	/**
	 * Get basic server info.
	 *
	 * @return  array
	 *
	 * @since   1.0.1
	 */
	public function getList()
	{
		return array(
			'account' => array(
				'baseUri' => JUri::base(),
				'currentUri' => JUri::current()
			)
		);
	}
}
