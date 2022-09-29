<?php
/**
 * @copyright Copyright (C) 2019 Phrase a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
class MemsourceConnectorApiArticles extends MemsourceConnectorApiContent
{
	/**
	 * Get instance of the model class.
	 *
	 * @return  MemsourceConnectorModelsArticles
	 *
	 * @since   1.0.0
	 */
	protected function getModel()
	{
		return $this->getModelInstance('articles');
	}
}
