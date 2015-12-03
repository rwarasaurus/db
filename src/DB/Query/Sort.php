<?php

namespace DB\Query;

use DB\GrammarInterface;

class Sort implements FragmentInterface {

	protected $grammer;

	protected $column;

	protected $mode;

	public function __construct($column, $mode, GrammarInterface $grammer) {
		$this->grammer = $grammer;
		$this->column = $column;
		$this->mode = $mode;
	}

	public function getSqlString() {
		$column = $this->grammer->column($this->column);

		return sprintf('ORDER BY %s %s', $column, $this->mode);
	}

	public function getBindings() {
		return [];
	}

}
