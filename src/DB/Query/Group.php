<?php

namespace DB\Query;

use DB\GrammarInterface;

class Group implements FragmentInterface {

	protected $grammer;

	protected $column;

	public function __construct($column, GrammarInterface $grammer) {
		$this->grammer = $grammer;
		$this->column = $column;
	}

	public function getSqlString() {
		$column = $this->grammer->column($this->column);

		return sprintf('GROUP BY %s', $column);
	}

}
