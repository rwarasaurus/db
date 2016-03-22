<?php

namespace DB;

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
	public function __get($key) {
		return array_key_exists($key, $this->attributes) ? $this->attributes[$key] : null;
	}

	/**
	 * Set attribute
	 *
	 * @param string
	 * @param mixed
	 */
	public function __set($key, $value) {
		$this->attributes[$key] = $value;
	}

	/**
	 * Check if attribute exists
	 *
	 * @param string
	 * @return bool
	 */
	public function __isset($key) {
		return array_key_exists($key, $this->attributes);
	}

	/**
	 * Disable unset attributes
	 *
	 * @param string
	 * @return bool
	 */
	public function __unset($key) {
		throw new \RuntimeException(sprintf('Cannot unset attributes "%s"', $key));
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
		return json_encode($this->jsonSerialize());
	}

	/**
	 * Convert to json string
	 *
	 * @return string
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}

	/**
	 * Return attributes
	 */
	public function toArray() {
		return $this->attributes;
	}

}
