<?php

namespace DB\Query;

use DB\GrammarInterface;

class Sort implements FragmentInterface {

	protected $grammar;

	protected $columns;

	public function __construct(GrammarInterface $grammar) {
		$this->grammar = $grammar;
		$this->columns = [];
	}

	public function by($column, $mode = 'asc') {
		$this->columns[] = sprintf('%s %s', $this->grammar->column($column), strtoupper($mode));
	}

	public function getSqlString() {
		if(empty($this->columns)) return '';

		return sprintf('ORDER BY %s', implode(', ', $this->columns));
	}

}
