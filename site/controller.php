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
	const HTTP_METHOD_GET = 'GET';
	const HTTP_METHOD_POST = 'POST';

	const STATUS_OK = 'OK';
	const STATUS_ERROR = 'ERROR';

	const HTTP_STATUS_BAD_REQUEST = 400;
	const HTTP_STATUS_UNAUTHORIZED = 401;
	const HTTP_STATUS_ERROR = 500;

	/**
	 * Method to display a view.
	 *
	 * @param   boolean       $cachable  If true, the view output will be cached
	 * @param   array|boolean $urlparams An array of safe url parameters and their variable types.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 * @since   1.0.0
	 */
	public function display($cachable = false, $urlparams = false)
	{
		$app = JFactory::getApplication();

		$method = $app->input->getMethod();
		$token = $app->input->get('token');
		$resource = $app->input->get('resource');
		$id = $app->input->get('id');

		$exception = null;

		try
		{
			$this->checkArguments($token, $resource);
			$this->checkAccessToken($token);
			$apiHandler = $this->createHandler($resource);

			if ($method === self::HTTP_METHOD_POST)
			{
				$lang = $app->input->get('target_language');
				$title = $app->input->post->get('title', null, 'raw');
				$content = $app->input->post->get('content', null, 'raw');
				$result = $apiHandler->storeTranslation($id, $lang, $title, $content);
			}
			elseif ($method === self::HTTP_METHOD_GET && $id !== null)
			{
				$result = $apiHandler->getDetail($id);
			}
			elseif ($method === self::HTTP_METHOD_GET)
			{
				$result = $apiHandler->getList();
			}
			else
			{
				throw new Exception("Method '$method' not supported for resource '$resource'.");
			}

			$response = array('status' => self::STATUS_OK) + $result;
		}
		catch (\Exception $exception)
		{
			if ($exception->getCode() !== 0)
			{
				http_response_code($exception->getCode());
			}
			else
			{
				http_response_code(self::HTTP_STATUS_ERROR);
			}

			$response = array(
				'status' => self::STATUS_ERROR,
				'message' => 'Unable to process request: ' . $exception->getMessage()
			);
		}

		/** @var MemsourceConnectorModelsDebug $debug */
		$debug = JModelLegacy::getInstance('debug', MEMSOURCE_CONNECTOR_MODEL);

		if ($debug->isEnabled())
		{
			MemsourceConnectorModelsLogger::logHttpRequest($app->input, $response, $exception);
		}

		header('Content-type: application/json');
		echo json_encode($response);
		jexit();
	}

	/**
	 * Create API handler for given resource type.
	 *
	 * @param   string $resource Resource type.
	 *
	 * @return  MemsourceConnectorApiInterface
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	private function createHandler($resource)
	{
		$class = 'MemsourceConnectorApi' . ucfirst($resource);

		if (!class_exists($class))
		{
			throw new Exception("Class '$class' not found.");
		}

		return new $class;
	}

	/**
	 * Check that request contains required arguments.
	 *
	 * @param   string $token    Access token.
	 * @param   string $resource Resource type.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	private function checkArguments($token, $resource)
	{
		if (!$token)
		{
			throw new Exception("Missing required argument 'token'.", self::HTTP_STATUS_BAD_REQUEST);
		}

		if (!$resource)
		{
			throw new Exception("Missing required argument 'resource'.", self::HTTP_STATUS_BAD_REQUEST);
		}
	}

	/**
	 * Check that access token is valid.
	 *
	 * @param   string $token Token from request.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	private function checkAccessToken($token)
	{
		/** @var MemsourceConnectorModelsToken $tokenService */
		$tokenService = JModelLegacy::getInstance('token', MEMSOURCE_CONNECTOR_MODEL);

		if (!$tokenService->isValid($token))
		{
			throw new Exception('Invalid token.', self::HTTP_STATUS_UNAUTHORIZED);
		}
	}
}
