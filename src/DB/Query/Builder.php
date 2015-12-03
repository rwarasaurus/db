<?php

namespace DB\Query;

use DB\Grammar;

class Builder implements BuilderInterface {

	protected $grammar;

	protected $select;

	protected $table;

	protected $joins = [];

	protected $where;

	public function __construct() {
		$this->grammar = new Grammar('sqlite');
		$this->where = new Where($this->grammar);
	}

	public function select(array $columns) {
		$this->select = new Select($columns, $this->grammar);

		return $this;
	}

	public function table($table) {
		$this->table = new Table($table, $this->grammar);

		return $this;
	}

	public function join($table, $left, $op, $right, $type = 'INNER') {
		$join = new Join($table, $type, $this->grammar);

		$this->joins[] = $join->constrant($left, $op, $right);

		return $this;
	}

	public function leftJoin($table, $left, $op, $right) {
		return $this->join($table, $left, $op, $right, 'LEFT');
	}

	public function joinColumns($table, array $columns, $type = 'INNER') {
		$join = new Join($table, $type, $this->grammar);

		foreach($columns as $left => $right) {
			$join->constrant($left, '=', $right);
		}

		$this->joins[] = $join;

		return $this;
	}

	public function leftJoinColumns($table, array $columns) {
		return $this->joinColumns($table, $columns, 'LEFT');
	}

	public function where($left, $op, $right) {
		$this->where->and($left, $op, $right);

		return $this;
	}

	public function orWhere($left, $op, $right) {
		$this->where->or($left, $op, $right);

		return $this;
	}

	public function whereNested(\Closure $predicate, $type = 'AND') {
		$this->where->nest($type);

		$predicate($this->where);

		$this->where->unnest();

		return $this;
	}

	public function orWhereNested(\Closure $predicate) {
		return $this->whereNested($predicate, 'OR');
	}

	public function whereIn($column, array $values) {
		$this->where->in($column, $values);

		return $this;
	}

	public function whereNotIn($column, array $values) {
		$this->where->notIn($column, $values);

		return $this;
	}

	protected function subQuery(\Closure $predicate) {
		$query = new static;

		$predicate($query);

		return $query;
	}

	public function whereInQuery($column, \Closure $predicate) {
		$this->where->inQuery($column, $this->subQuery($predicate));

		return $this;
	}

	public function whereNotInQuery($column, \Closure $predicate) {
		$this->where->notInQuery($column, $this->subQuery($predicate));

		return $this;
	}

	public function group($column) {
		$this->groups[] = new Group($column, $this->grammar);

		return $this;
	}

	public function sort($column, $mode = 'ASC') {
		$this->sorts[] = new Sort($column, $mode, $this->grammar);

		return $this;
	}

	public function getSqlString() {
		$fragments = [];

		$fragments[] = $this->select->getSqlString();

		$fragments[] = $this->table->getSqlString();

		foreach($this->joins as $fragment) {
			$fragments[] = $fragment->getSqlString();
		}

		if($this->where) {
			$fragments[] = $this->where->getSqlString();
		}

		return implode(' ', $fragments);
	}

	public function getBindings() {
		$bindings = [];

		foreach($this->joins as $fragment) {
			$bindings = array_merge($bindings, $fragment->getBindings());
		}

		if($this->where) {
			$bindings = array_merge($bindings, $this->where->getBindings());
		}

		return $bindings;
	}

}
