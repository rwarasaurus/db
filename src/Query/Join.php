<?php

namespace DB\Query;

use DB\GrammarInterface;

class Join extends AbstractWrapper implements FragmentInterface, BindingsInterface {

	protected $table;

	protected $type;

	protected $grammar;

	protected $needsConjunction;

	protected $constraints;

	protected $bindings;

	public function __construct($table, $type, GrammarInterface $grammar) {
		$this->table = $table;
		$this->type = $type;
		$this->grammar = $grammar;
		$this->needsConjunction = false;
		$this->constraints = [];
		$this->bindings = [];
	}

	public function constraint($left, $op, $right, $type = 'AND') {
		if($this->needsConjunction) {
			$this->constraints[] = $type;
		}

		$this->constraints[] = sprintf('%s %s %s', $this->grammar->column($left), $op, $this->grammar->column($right));

		$this->needsConjunction = true;

		return $this;
	}

	public function where($left, $op, $right, $type = 'AND') {
		if($this->needsConjunction) {
			$this->constraints[] = $type;
		}

		$this->constraints[] = sprintf('%s %s %s', $this->grammar->column($left), $op, $this->wrap($right));

		$this->needsConjunction = true;

		return $this;
	}

	public function __invoke($left, $op, $right) {
		return $this->constraint($left, $op, $right);
	}

	public function __call($method, array $args) {
		if( ! in_array($method, ['and', 'or'])) {
			throw new \RuntimeException(sprintf('Undefined method "%s".', $method));
		}

		$args[] = strtoupper($method);

		return call_user_func_array([$this, 'constraint'], $args);
	}

	public function getSqlString() {
		$table = $this->table instanceof BuilderInterface ?
			$this->wrap($this->table) : $this->grammar->wrap($this->table);

		$constraints = implode(' ', $this->constraints);

		return sprintf('%s JOIN %s ON(%s)', $this->type, $table, $constraints);
	}

	public function getBindings() {
		return $this->bindings;
	}

}
