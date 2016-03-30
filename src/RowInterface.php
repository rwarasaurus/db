<?php

namespace DB;

interface RowInterface extends \JsonSerializable, \Serializable {

	public function __get($column);

	public function __set($column, $value);

	public function __toString();

	public function toArray();

}
