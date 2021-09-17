<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.1.0
 */
class MemsourceConnectorApiEasyblogPosts extends MemsourceConnectorApiContent
{
	/**
	 * Get instance of the model class.
	 *
	 * @return  MemsourceConnectorModelsEasyblogPosts
	 *
	 * @since   1.1.0
	 */
	protected function getModel()
	{
		return $this->getModelInstance('posts', 'Easyblog');
	}
}
