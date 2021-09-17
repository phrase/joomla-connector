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
abstract class MemsourceConnectorModelsDtoResult implements JsonSerializable
{
	/**
	 * @var   string
	 *
	 * @since 1.0.0
	 */
	protected $id;

	/**
	 * @var   string
	 *
	 * @since 1.0.0
	 */
	protected $title;

	/**
	 * Get ID of the article.
	 *
	 * @return string
	 *
	 * @since  1.0.0
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set ID of the article.
	 *
	 * @param   string $id Article ID
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Get title of the article.
	 *
	 * @return string
	 *
	 * @since  1.0.0
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Set title of the article.
	 *
	 * @param   string $title Article title
	 *
	 * @return  void
	 *
	 * @since   1.1.0
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * Return data which should be serialized by json_encode().
	 *
	 * @return array
	 *
	 * @since  1.0.0
	 */
	public function jsonSerialize()
	{
		return
			array(
				'id' => $this->id,
				'title' => $this->title
			)
			+
			get_object_vars($this);
	}
}
