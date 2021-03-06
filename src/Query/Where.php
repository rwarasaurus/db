<?php

namespace DB\Query;

use DB\GrammarInterface;

class Where extends AbstractWrapper implements FragmentInterface, BindingsInterface {

	protected $grammar;

	protected $constraints;

	protected $bindings;

	protected $needsConjunction;

	public function __construct(GrammarInterface $grammar) {
		$this->grammar = $grammar;
		$this->needsConjunction = false;
		$this->constraints = [];
		$this->bindings = [];
	}

	public function constraint(string $left, string $op, $right, string $type = 'AND') {
		if($this->needsConjunction) {
			$this->constraints[] = $type;
		}

		$this->constraints[] = sprintf('%s %s %s',
			$this->grammar->column($left), $op, $this->wrap($right));

		$this->needsConjunction = true;

		return $this;
	}

	public function match(string $keywords, array $columns, string $mode, string $type = 'AND') {
		if($this->needsConjunction) {
			$this->constraints[] = $type;
		}

		$this->constraints[] = sprintf('MATCH(%s) AGAINST(%s IN %s MODE)',
			$this->grammar->columns($columns), $this->wrap($keywords), strtoupper($mode));

		$this->needsConjunction = true;

		return $this;
	}

	public function orMatch(string $keywords, array $columns, string $mode) {
		return $this->match($keywords, $columns, $mode, 'OR');
	}

	public function __call(string $method, array $args) {
		if( ! in_array($method, ['and', 'or'])) {
			throw new \RuntimeException('Undefined method.');
		}

		$args[] = strtoupper($method);

		return call_user_func_array([$this, 'constraint'], $args);
	}

	public function __invoke($left, $op, $right) {
		return $this->constraint($left, $op, $right);
	}

	public function nest(string $type = 'AND') {
		if($this->needsConjunction) {
			$this->constraints[] = $type;
		}

		$this->constraints[] = '(';

		$this->needsConjunction = false;

		return $this;
	}

	public function unnest() {
		$this->constraints[] = ')';

		$this->needsConjunction = true;

		return $this;
	}

	public function in($column, array $values) {
		return $this->constraint($column, 'IN', $values);
	}

	public function orIn($column, array $values) {
		return $this->constraint($column, 'IN', $values, 'OR');
	}

	public function notIn($column, array $values) {
		return $this->constraint($column, 'NOT IN', $values);
	}

	public function orNotIn($column, array $values) {
		return $this->constraint($column, 'NOT IN', $values, 'OR');
	}

	public function inQuery($column, BuilderInterface $query) {
		return $this->constraint($column, 'IN', $query);
	}

	public function orInQuery($column, BuilderInterface $query) {
		return $this->constraint($column, 'IN', $query, 'OR');
	}

	public function notInQuery($column, BuilderInterface $query) {
		return $this->constraint($column, 'NOT IN', $query);
	}

	public function orNotInQuery($column, BuilderInterface $query) {
		return $this->constraint($column, 'NOT IN', $query, 'OR');
	}

	public function isNull($column) {
		return $this->constraint($column, 'IS', null);
	}

	public function orIsNull($column) {
		return $this->constraint($column, 'IS', null, 'OR');
	}

	public function isNotNull($column) {
		return $this->constraint($column, 'IS NOT', null);
	}

	public function orIsNotNull($column) {
		return $this->constraint($column, 'IS NOT', null, 'OR');
	}

	public function getSqlString(): string {
		if(empty($this->constraints)) return '';

		return 'WHERE ' . implode(' ', $this->constraints);
	}

	public function getBindings(): array {
		return $this->bindings;
	}

}
