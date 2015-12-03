<?php

namespace DB\Query;

use DB\GrammarInterface;

class Table implements FragmentInterface {

	protected $table;

	protected $grammer;

	public function __construct($table, GrammarInterface $grammer) {
		$this->table = $table;
		$this->grammer = $grammer;
	}

	public function getSqlString() {
		$table = $this->grammer->column($this->table);

		return sprintf('FROM %s', $table);
	}

}
