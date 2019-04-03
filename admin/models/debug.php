<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Setup debug mode of Memsource Connector.
 *
 * @since 1.0.0
 */
class MemsourceConnectorModelsDebug extends MemsourceConnectorModelsParams
{
	const PARAM_NAME = 'debug';

	const DEBUG_ENABLED = 'enabled';
	const DEBUG_DISABLED = 'disabled';

	/**
	 * Enable debug mode for Memsource Connector.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function enable()
	{
		$this->setParam(self::PARAM_NAME, self::DEBUG_ENABLED);
	}

	/**
	 * Disable debug mode for Memsource Connector.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function disable()
	{
		$this->setParam(self::PARAM_NAME, self::DEBUG_DISABLED);
	}

	/**
	 * Check if debug mode = enabled.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function isEnabled()
	{
		return $this->getParam(self::PARAM_NAME) === self::DEBUG_ENABLED;
	}
}
