<?php
/**
 * @copyright Copyright (C) 2019 Phrase a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
class MemsourceConnectorViewSettings extends JViewLegacy
{
	/**
	 * Display page using template.
	 *
	 * @param   string $tpl The name of the template file to parse.
	 *
	 * @return  boolean|void
	 *
	 * @since   1.0.0
	 */
	public function display($tpl = null)
	{
		foreach ($this->get('Errors') ?: array() as $error)
		{
			JFactory::getApplication()->enqueueMessage($error, 'error');
		}

		/** @var MemsourceConnectorModelsToken $token */
		$token = JModelLegacy::getInstance('token', MEMSOURCE_CONNECTOR_MODEL);
		$this->token = $token->getValue();

		if ($this->token === null)
		{
			$this->token = $token->createNewAccessToken();
		}

		/** @var MemsourceConnectorModelsDebug $debug */
		$debug = JModelLegacy::getInstance('debug', MEMSOURCE_CONNECTOR_MODEL);
		$this->debug = $debug->isEnabled();

		$this->logFileSize = MemsourceConnectorModelsLogger::getLogFileSize();

		JFactory::getDocument()->addStyleDeclaration(
			' .icon-phrase { 
				background-image: url("components/com_memsource_connector/images/phrase.svg");
				background-repeat: no-repeat;
				width: 17px;
				height: 25px;
				background-size: 100% 100%;
				position: relative;
				top: 6px;
				left: 5px;
			} '
		);

		JToolBarHelper::title(JText::_('COM_MEMSOURCE_CONNECTOR'), 'phrase');

		parent::display($tpl);
	}
}
