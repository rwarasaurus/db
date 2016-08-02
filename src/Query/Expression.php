<?php

namespace DB\Query;

class Expression implements FragmentInterface {

	protected $string;

	public function __construct(string $string) {
		$this->string = $string;
	}

	public function getSqlString(): string {
		return $this->string;
	}

}
