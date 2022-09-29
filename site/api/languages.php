<?php
/**
 * @copyright Copyright (C) 2019 Phrase a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
class MemsourceConnectorApiLanguages extends MemsourceConnectorApiBase
{
	/**
	 * Get list of languages.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	public function getList()
	{
		$contentLanguages = JLanguageHelper::getContentLanguages();
		$languages = array();

		foreach ($contentLanguages as $code => $lang)
		{
			$languages[] = array(
				'code' => MemsourceConnectorHelpersLanguage::normalizeLangCode($code),
				'title' => $lang->title,
				'source' => MemsourceConnectorHelpersLanguage::isDefaultLangCode($code)
			);
		}

		return array('languages' => $languages);
	}
}
