<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

/**
 * Request/response logger.
 *
 * @since 1.0.0
 */
class MemsourceConnectorModelsLogger
{
	const LOG_FILE = 'com_memsource_connector.joomla.log';
	const LOG_FILE_RECIPIENT = 'integrations@memsource.com';

	/**
	 * Log HTTP request and response.
	 *
	 * @param   JInput    $input     Input from Joomla app.
	 * @param   array     $response  Response that will be send to the client.
	 * @param   Exception $exception In case that exception was thrown, here can be added to the log entry.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function logHttpRequest($input, $response, $exception = null)
	{
		$message = "HTTP-REQUEST\n";
		$message .= "\nREQUEST:\n";
		$message .= $input->getMethod() . ' ' . self::getCurrentUrl($input->get) . "\n";

		if ($input->getMethod() === 'POST')
		{
			$message .= "Post data:\n" . self::getPostData($input->post);
		}

		if ($exception !== null)
		{
			$message .= "\nEXCEPTION:\nThrown $exception\n";
		}

		$message .= "\nRESPONSE:\n" . self::formatResponse($response);
		$message .= "\n------------------\n";

		self::addJlogEntry($message);
	}

	/**
	 * Get size of logs in bytes.
	 *
	 * @return  integer
	 *
	 * @since   1.0.0
	 */
	public static function getLogFileSize()
	{
		$logFile = self::getLogFileNameWithPath();

		if (file_exists($logFile))
		{
			return filesize($logFile);
		}

		return 0;
	}

	/**
	 * Force download log file in web browser.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function forceDownloadLogFile()
	{
		self::logSystemInfo();

		header("Content-Type: text/plain\n");
		header('Content-Disposition: attachment; filename="' . self::getLogFileNameForDownload() . '"\n');
		readfile(self::getLogFileNameWithPath());
		jexit();
	}

	/**
	 * Send logs to Memsource.
	 *
	 * @return  boolean|Jexception
	 *
	 * @since   1.0.0
	 */
	public static function sendLogFileToEmail()
	{
		self::logSystemInfo();

		$config = JFactory::getConfig();
		$mailer = JFactory::getMailer();
		$mailer->setSender(array($config->get('mailfrom'), $config->get('fromname')));
		$mailer->setSubject('Joomla logs from ' . JUri::base());
		$mailer->setBody('Joomla logs from ' . JUri::base());
		$mailer->addRecipient(self::LOG_FILE_RECIPIENT);
		$mailer->addAttachment(self::getLogFileNameWithPath());

		return $mailer->Send();
	}

	/**
	 * Delete logs.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	public static function deleteLogFile()
	{
		JFile::delete(self::getLogFileNameWithPath());
	}

	/**
	 * Build URL string.
	 *
	 * @param   JInput $get GET data.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private static function getCurrentUrl($get)
	{
		$args = array();

		foreach ($get->getArray() as $key => $value)
		{
			$args[] = "$key=$value";
		}

		$args = implode('&', $args);

		return JUri::current() . "?$args";
	}

	/**
	 * Create string with POST data.
	 *
	 * @param   JInput $post POST data.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private static function getPostData($post)
	{
		$postData = '';

		foreach ($post->getArray() as $key => $value)
		{
			$postData .= "  - $key: $value\n";
		}

		return $postData;
	}

	/**
	 * Build formatted response.
	 *
	 * @param   array $response Formatted response.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private static function formatResponse($response)
	{
		return json_encode($response, JSON_PRETTY_PRINT) . "\n";
	}

	/**
	 * Log Joomla and extension versions.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private static function logSystemInfo()
	{
		self::addJlogEntry('Joomla version: ' . (new JVersion)->getLongVersion());
		$component = \JComponentHelper::getComponent('com_memsource_connector');
		$extension = \JTable::getInstance('extension');
		$extension->load($component->id);
		$manifest = new \Joomla\Registry\Registry($extension->manifest_cache);
		self::addJlogEntry('Memsource extension version: ' . $manifest->get('version'));
	}

	/**
	 * Add new entry to the log file.
	 *
	 * @param   string $message Log entry.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private static function addJlogEntry($message)
	{
		jimport('joomla.log.logger.formattedtext');
		$config = array('text_file' => self::LOG_FILE);
		$logger = new JLogLoggerFormattedtext($config);
		$entry = new JLogEntry($message, JLog::INFO);
		$logger->addEntry($entry);
	}

	/**
	 * Get log file with absolute path.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private static function getLogFileNameWithPath()
	{
		return JFactory::getApplication()->get('log_path') . '/' . self::LOG_FILE;
	}

	/**
	 * Get name of downloaded file.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private static function getLogFileNameForDownload()
	{
		return date('Y-m-d_H-i-s') . '_' . self::LOG_FILE;
	}
}
