<?php

namespace DB\Traits;

use DB\RowInterface;

trait PrototypeHydrator {

	protected $prototype;

	public function prototype(RowInterface $prototype) {
		$this->prototype = $prototype;

		return $this;
	}

	public function hydrate(array $row) {
		$obj = clone $this->prototype;

		foreach($row as $key => $value) {
			$obj->$key = $value;
		}

		return $obj;
	}

}
