<?php
/**
 * @copyright Copyright (C) 2019 Memsource a.s. All rights reserved.
 * @license   GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Base class that implements basic DTO's functionality.
 *
 * @since 1.0.0
 */
class MemsourceConnectorModelsDtoItem extends MemsourceConnectorModelsDtoResult
{
	/**
	 * @var   string
	 *
	 * @since 1.0.0
	 */
	protected $modified;

	/**
	 * @var   string
	 *
	 * @since 1.0.0
	 */
	protected $size;

	/**
	 * Get body of the article.
	 *
	 * @return string
	 *
	 * @since  1.0.0
	 */
	public function getModified()
	{
		return $this->modified;
	}

	/**
	 * Get body of the article.
	 *
	 * @return string
	 *
	 * @since  1.0.0
	 */
	public function getSize()
	{
		return $this->size;
	}
}
