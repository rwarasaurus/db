<?php

namespace DB\Query;

use DB\GrammarInterface;

class Update implements FragmentInterface, BindingsInterface {

	protected $grammar;

	protected $table;

	protected $values;

	protected $bindings;

	public function __construct($table, array $values, GrammarInterface $grammar) {
		$this->grammar = $grammar;
		$this->table = $table;
		$this->values = [];
		$this->bindings = [];
		$this->setValues($values);
	}

	public function setValues(array $values) {
		foreach($values as $key => $value) {
			if($value instanceof FragmentInterface) {
				$this->values[] = sprintf('%s = %s', $this->grammar->column($key), $value->getSqlString());
			}
			else {
				$this->values[] = sprintf('%s = ?', $this->grammar->column($key));
				$this->bindings[] = $value;
			}
		}
	}

	public function getSqlString(): string {
		$table = $this->grammar->wrap($this->table);

		$placeholders = implode(', ', $this->values);

		return sprintf('UPDATE %s SET %s', $table, $placeholders);
	}

	public function getBindings(): array {
		return $this->bindings;
	}

}
