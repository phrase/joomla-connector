<?php
/**
 * @copyright Copyright (C) 2019 Phrase a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
class MemsourceConnectorHelpersLanguage
{
	/**
	 * Convert language code to narmalized form: 'en-GB' -> 'en_gb'.
	 *
	 * @param   string $langCode Source language code.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function normalizeLangCode($langCode)
	{
		$langCode = str_replace('-', '_', $langCode);
		$langCode = strtolower($langCode);

		return $langCode;
	}

	/**
	 * Convert language code from Memsource to Joomla format: 'en_gb' -> 'en-GB'.
	 *
	 * @param   string $lang Memsource language format: 'de_de'.
	 *
	 * @return  string Joomla language format: 'de-DE'.
	 *
	 * @since   1.0.0
	 */
	public static function denormalizeLangCode($lang)
	{
		$exploded = explode('_', $lang, 2);

		if (isset($exploded[1]))
		{
			return $exploded[0] . '-' . strtoupper($exploded[1]);
		}

		return $lang;
	}

	/**
	 * Check that given language is default content language.
	 *
	 * @param   string $langCode Source language code, for example 'en-GB'.
	 *
	 * @return  string
	 *
	 * @since   1.0.0
	 */
	public static function isDefaultLangCode($langCode)
	{
		return $langCode === JFactory::getLanguage()->getDefault();
	}
}
