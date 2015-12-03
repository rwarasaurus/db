<?php

namespace DB\Query;

use DB\GrammarInterface;

class Where extends AbstractWrapper implements FragmentInterface, BindingsInterface {

	protected $grammar;

	protected $constrants;

	protected $bindings;

	protected $needsConjunction;

	public function __construct(GrammarInterface $grammar) {
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

	public function and($left, $op, $right) {
		return $this->constrant($left, $op, $right);
	}

	public function __invoke($left, $op, $right) {
		return $this->constrant($left, $op, $right);
	}

	public function or($left, $op, $right) {
		return $this->constrant($left, $op, $right, 'OR');
	}

	public function nest($type = 'AND') {
		if($this->needsConjunction) {
			$this->constrants[] = $type;
		}

		$this->constrants[] = '(';

		$this->needsConjunction = false;

		return $this;
	}

	public function unnest() {
		$this->constrants[] = ')';

		$this->needsConjunction = true;

		return $this;
	}

	public function in($column, array $values) {
		return $this->constrant($column, 'IN', $values);
	}

	public function orIn($column, array $values) {
		return $this->constrant($column, 'IN', $values, 'OR');
	}

	public function notIn($column, array $values) {
		return $this->constrant($column, 'NOT IN', $values);
	}

	public function orNotIn($column, array $values) {
		return $this->constrant($column, 'NOT IN', $values, 'OR');
	}

	public function inQuery($column, BuilderInterface $query) {
		return $this->constrant($column, 'IN', $query);
	}

	public function notInQuery($column, BuilderInterface $query) {
		return $this->constrant($column, 'NOT IN', $query);
	}

	public function isNull($column) {
		return $this->constrant($column, 'IS', null);
	}

	public function orIsNull($column) {
		return $this->constrant($column, 'IS', null, 'OR');
	}

	public function isNotNull($column) {
		return $this->constrant($column, 'IS NOT', null);
	}

	public function orIsNotNull($column) {
		return $this->constrant($column, 'IS NOT', null, 'OR');
	}

	public function getSqlString() {
		if(empty($this->constrants)) return '';

		return 'WHERE ' . implode(' ', $this->constrants);
	}

	public function getBindings() {
		return $this->bindings;
	}

}
