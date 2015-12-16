<?php

namespace DB\Query;

use DB\GrammarInterface;

class Join extends AbstractWrapper implements FragmentInterface, BindingsInterface {

	protected $table;

	protected $grammar;

	protected $constrants;

	protected $bindings;

	protected $needsConjunction;

	public function __construct($table, $type, GrammarInterface $grammar) {
		$this->table = $table;
		$this->type = $type;
		$this->grammar = $grammar;
		$this->needsConjunction = false;
		$this->constrants = [];
		$this->bindings = [];
	}

	public function constrant($left, $op, $right, $type = 'AND') {
		if($this->needsConjunction) {
			$this->constrants[] = $type;
		}

		$this->constrants[] = sprintf('%s %s %s', $this->grammar->column($left), $op, $this->wrap($right));

		$this->needsConjunction = true;

		return $this;
	}

	public function __invoke($left, $op, $right) {
		return $this->constrant($left, $op, $right);
	}

	public function __call($method, array $args) {
		if( ! in_array($method, ['and', 'or'])) {
			throw new \RuntimeException('Undefined method.');
		}

		$args[] = strtoupper($method);

		return call_user_func_array([$this, 'constrant'], $args);
	}

	public function getSqlString() {
		$table = $this->table instanceof BuilderInterface ? $this->wrap($this->table) : $this->grammar->wrap($this->table);

		$constrants = implode(' ', $this->constrants);

		return sprintf('%s JOIN %s ON(%s)', $this->type, $table, $constrants);
	}

	public function getBindings() {
		return $this->bindings;
	}

}
