<?php

namespace DB\Contracts;

interface Row {

	public function __get($column);

	public function __set($column, $value);

	public function __toString();

	public function toArray();

	public function toJson();

}
