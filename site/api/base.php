<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * @since 1.0.0
 */
abstract class MemsourceConnectorApiBase implements MemsourceConnectorApiInterface
{
	const CODE_UNSUPPORTED_OPERATION = 400;

	/**
	 * Get list of given resource.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getList()
	{
		throw new Exception('Unsupported operation.', self::CODE_UNSUPPORTED_OPERATION);
	}

	/**
	 * Get single resource by ID.
	 *
	 * @param   int $id ID.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function getDetail($id)
	{
		throw new Exception('Unsupported operation.', self::CODE_UNSUPPORTED_OPERATION);
	}

	/**
	 * Store translated content.
	 *
	 * @param   int    $id      ID.
	 * @param   string $lang    Target language.
	 * @param   string $title   Translated title.
	 * @param   string $content Translated content.
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public function storeTranslation($id, $lang, $title, $content)
	{
		throw new Exception('Unsupported operation.', self::CODE_UNSUPPORTED_OPERATION);
	}

	/**
	 * Get instance of model class.
	 *
	 * @param   string $type      Model type.
	 * @param   string $namespace Namespace of the model class.
	 *
	 * @return  MemsourceConnectorModelsContent
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	protected function getModelInstance($type, $namespace = '')
	{
		$prefix = MEMSOURCE_CONNECTOR_MODEL;

		if ($namespace !== '')
		{
			$prefix .= $namespace;
		}

		$model = JModelLegacy::getInstance($type, $prefix);

		if ($model === false)
		{
			throw new Exception("Model '$type' not found");
		}

		return $model;
	}
}
