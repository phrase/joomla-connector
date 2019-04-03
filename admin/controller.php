<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
class MemsourceConnectorController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean       $cachable  If true, the view output will be cached
	 * @param   array|boolean $urlparams An array of safe url parameters and their variable types.
	 *
	 * @return  MemsourceConnectorController This object to support chaining.
	 *
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$view = JFactory::getApplication()->input->getCmd('view', 'settings');
		JFactory::getApplication()->input->set('view', $view);

		parent::display($cachable, $urlparams);

		return $this;
	}

	/**
	 * Create and store new access token.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function generateToken()
	{
		/** @var MemsourceConnectorModelsToken $token */
		$token = JModelLegacy::getInstance('token', MEMSOURCE_CONNECTOR_MODEL);
		$token->createNewAccessToken();
		$this->redirectWithMessage('COM_MEMSOURCE_CONNECTOR_GENERATE_TOKEN_OK');
	}

	/**
	 * Enable debug mode.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function enableDebug()
	{
		/** @var MemsourceConnectorModelsDebug $debug */
		$debug = JModelLegacy::getInstance('debug', MEMSOURCE_CONNECTOR_MODEL);
		$debug->enable();
		$this->redirectWithMessage('COM_MEMSOURCE_CONNECTOR_ENABLE_DEBUG_MODE_OK');
	}

	/**
	 * Disable debug mode.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function disableDebug()
	{
		/** @var MemsourceConnectorModelsDebug $debug */
		$debug = JModelLegacy::getInstance('debug', MEMSOURCE_CONNECTOR_MODEL);
		$debug->disable();
		$this->redirectWithMessage('COM_MEMSOURCE_CONNECTOR_DISABLE_DEBUG_MODE_OK');
	}

	/**
	 * Force download file with logs.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function downloadLogs()
	{
		MemsourceConnectorModelsLogger::forceDownloadLogFile();
		jexit();
	}

	/**
	 * Send logs to Memsource using e-mail.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function sendLogs()
	{
		$result = MemsourceConnectorModelsLogger::sendLogFileToEmail();

		if ($result === true)
		{
			$this->redirectWithMessage('COM_MEMSOURCE_CONNECTOR_SEND_LOGS_OK');
		}
		else
		{
			$this->redirectWithMessage();
		}
	}

	/**
	 * Delete file with logs.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public function clearLogs()
	{
		MemsourceConnectorModelsLogger::deleteLogFile();
		$this->redirectWithMessage('COM_MEMSOURCE_CONNECTOR_CLEAR_LOGS_OK');
	}

	/**
	 * Redirect to ?option=com_memsource_connector and display message.
	 *
	 * @param   string|null $message Message with result of the operation.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function redirectWithMessage($message = null)
	{
		$app = JFactory::getApplication();

		if ($message !== null)
		{
			$app->enqueueMessage(JText::_($message), 'Success');
		}

		$app->redirect(JRoute::_('index.php?option=com_memsource_connector'));
	}
}
