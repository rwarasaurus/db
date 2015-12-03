<?php

namespace DB\Query\Traits;

trait Join {

	protected $joins;

	public function join($table, $left, $op, $right, $type = 'INNER') {
		$join = new \DB\Query\Join($table, $type, $this->grammar);

		$this->joins[] = $join->constrant($left, $op, $right);

		return $this;
	}

	public function leftJoin($table, $left, $op, $right) {
		return $this->join($table, $left, $op, $right, 'LEFT');
	}

	public function joinColumns($table, array $columns, $type = 'INNER') {
		$join = new \DB\Query\Join($table, $type, $this->grammar);

		foreach($columns as $left => $right) {
			$join->constrant($left, '=', $right);
		}

		$this->joins[] = $join;

		return $this;
	}

	public function leftJoinColumns($table, array $columns) {
		return $this->joinColumns($table, $columns, 'LEFT');
	}

}
