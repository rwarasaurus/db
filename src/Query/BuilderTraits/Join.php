<?php

namespace DB\Query\BuilderTraits;

trait Join {

	protected $joins;

	public function join($table, $left, $op, $right, $type = 'INNER') {
		$join = new \DB\Query\Join($table, $type, $this->grammar);

		$this->joins[] = $join->constraint($left, $op, $right);

		return $this;
	}

	public function leftJoin($table, $left, $op, $right) {
		return $this->join($table, $left, $op, $right, 'LEFT');
	}

	public function joinWhere($table, \Closure $predicate, $type = 'INNER') {
		$join = new \DB\Query\Join($table, $type, $this->grammar);

		$predicate($join);

		$this->joins[] = $join;

		return $this;
	}

	public function leftJoinWhere($table, \Closure $predicate) {
		return $this->joinWhere($table, $predicate, 'LEFT');
	}

	public function joinColumns($table, array $columns, $type = 'INNER') {
		$join = new \DB\Query\Join($table, $type, $this->grammar);

		foreach($columns as $left => $right) {
			$join->constraint($left, '=', $right);
		}

		$this->joins[] = $join;

		return $this;
	}

	public function leftJoinColumns($table, array $columns) {
		return $this->joinColumns($table, $columns, 'LEFT');
	}

	public function joinQuery(\Closure $predicate, $alias, $left, $op, $right) {
		$this->join($this->subQuery($predicate, $alias), $left, $op, $right);
	}

	public function leftJoinQuery(\Closure $predicate, $alias, $left, $op, $right) {
		$this->leftJoin($this->subQuery($predicate, $alias), $left, $op, $right);
	}

}
