<?php

namespace DB;

use DB\Contracts\Row as RowInterface;

class Row implements RowInterface, \Serializable, \JsonSerializable {

	/**
	 * Row attributes
	 *
	 * @var array
	 */
	protected $attributes;

	/**
	 * Active Record constructor
	 *
	 * @param array
	 */
	public function __construct(array $attributes = null) {
		$this->attributes = null === $attributes ? [] : $attributes;
	}

	/**
	 * Get attribute
	 *
	 * @param string
	 * @return mixed
	 */
	public function __get($column) {
		return array_key_exists($column, $this->attributes) ? $this->attributes[$column] : null;
	}

	/**
	 * Set attribute
	 *
	 * @param string
	 * @param mixed
	 */
	public function __set($column, $value) {
		$this->attributes[$column] = $value;
	}

	/**
	 * Serialize attributes
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize($this->attributes);
	}

	/**
	 * Unserialize attributes
	 *
	 * @param string
	 */
	public function unserialize($data) {
		$this->attributes = unserialize($data);
	}

	/**
	 * Convert to string (json)
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->jsonSerialize();
	}

	/**
	 * Convert to json string
	 *
	 * @return string
	 */
	public function jsonSerialize() {
		return json_encode($this->attributes);
	}

	/**
	 * Return attributes
	 */
	public function toArray() {
		return $this->attributes;
	}

}
