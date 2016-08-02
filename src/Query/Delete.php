<?php

namespace DB\Query;

use DB\GrammarInterface;

class Delete implements FragmentInterface {

	protected $grammar;

	protected $table;

	public function __construct($table, GrammarInterface $grammar) {
		$this->grammar = $grammar;
		$this->table = $table;
	}

	public function getSqlString(): string {
		$table = $this->grammar->wrap($this->table);

		return sprintf('DELETE FROM %s', $table);
	}

}
