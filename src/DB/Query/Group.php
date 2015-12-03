<?php

namespace DB\Query;

use DB\GrammarInterface;

class Group implements FragmentInterface {

	protected $grammar;

	protected $columns;

	public function __construct(GrammarInterface $grammar) {
		$this->grammar = $grammar;
		$this->columns = [];
	}

	public function by($column) {
		$this->columns[] = $this->grammar->column($column);
	}

	public function getSqlString() {
		if(empty($this->columns)) return '';

		return sprintf('GROUP BY %s', implode(', ', $this->columns));
	}

}
