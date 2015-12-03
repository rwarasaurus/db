<?php

namespace DB\Query;

use DB\GrammarInterface;

class Insert implements FragmentInterface, BindingsInterface {

	protected $grammar;

	protected $table;

	protected $values;

	public function __construct($table, array $values, GrammarInterface $grammar) {
		$this->grammar = $grammar;
		$this->table = $table;
		$this->values = $values;
	}

	public function getSqlString() {
		$table = $this->grammar->wrap($this->table);

		$columns = $this->grammar->columns(array_keys($this->values));

		$placeholders = $this->grammar->placeholders($this->values);

		return sprintf('INSERT INTO %s (%s) VALUES(%s)', $table, $columns, $placeholders);
	}

	public function getBindings() {
		return array_values($this->values);
	}

}
