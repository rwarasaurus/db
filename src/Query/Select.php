<?php

namespace DB\Query;

use DB\GrammarInterface;

class Select implements FragmentInterface {

	protected $columns;

	protected $grammar;

	public function __construct(GrammarInterface $grammar) {
		$this->columns = [];
		$this->grammar = $grammar;
	}

	public function columns(array $columns) {
		$this->columns = $columns;
	}

	public function getSqlString(): string {
		if(empty($this->columns)) {
			$this->columns = ['*'];
		}

		foreach(array_keys($this->columns) as $index) {
			if($this->columns[$index] instanceof FragmentInterface) {
				$this->columns[$index] = $this->columns[$index]->getSqlString();
			}
		}

		return sprintf('SELECT %s', $this->grammar->columns($this->columns));
	}

}
