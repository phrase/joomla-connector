<?php
/**
 * @copyright Copyright (C) 2019 Phrase a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Generate and store token for remote access.
 *
 * @since 1.0.0
 */
class MemsourceConnectorModelsToken extends MemsourceConnectorModelsParams
{
	const PARAM_NAME = 'token';

	const TOKEN_LENGTH = 24;

	/**
	 * Get access token.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function getValue()
	{
		return $this->getParam(self::PARAM_NAME);
	}

	/**
	 * Check token.
	 *
	 * @param   string $value Validated token.
	 *
	 * @return  boolean
	 *
	 * @since   1.0.0
	 */
	public function isValid($value)
	{
		return $value === $this->getParam(self::PARAM_NAME);
	}

	/**
	 * Generate new access token and store into database.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public function createNewAccessToken()
	{
		$token = $this->generateRandomToken();
		$this->setParam(self::PARAM_NAME, $token);

		return $token;
	}

	/**
	 * Generate random access token.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	private function generateRandomToken()
	{
		$characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$token = '';
		$max = strlen($characters) - 1;

		for ($i = 0; $i < self::TOKEN_LENGTH; $i++)
		{
			$token .= $characters[mt_rand(0, $max)];
		}

		return $token;
	}
}
