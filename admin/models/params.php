<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Base class that stores and loads params from/to __extensions table.
 *
 * @since 1.0.0
 */
abstract class MemsourceConnectorModelsParams extends JModelLegacy
{
	/**
	 * Get value of single parameter.
	 *
	 * @param   string $name Parameter name.
	 *
	 * @return  string|null Parameter value.
	 *
	 * @since   1.0.0
	 */
	protected function getParam($name)
	{
		$params = $this->loadParams();

		if (isset($params[$name]))
		{
			return $params[$name];
		}

		return null;
	}

	/**
	 * Set value of single parameter.
	 *
	 * @param   string $name  Parameter name.
	 * @param   string $value Parameter value.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function setParam($name, $value)
	{
		$params = $this->loadParams();
		$params[$name] = $value;
		$this->storeParams($params);
	}

	/**
	 * Load component's params from database.
	 *
	 * @return  array
	 *
	 * @since   1.0.0
	 */
	private function loadParams()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('com_memsource_connector'));
		$db->setQuery($query);

		return json_decode($db->loadResult(), true);
	}

	/**
	 * Store component's params into database.
	 *
	 * @param   array $params Key-value pairs.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	private function storeParams($params)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);
		$query->update($db->quoteName('#__extensions'));
		$query->set($db->quoteName('params') . ' = ' . $db->quote(json_encode($params)));
		$query->where($db->quoteName('element') . ' = ' . $db->quote('com_memsource_connector'));
		$db->setQuery($query);
		$db->query();
	}
}
