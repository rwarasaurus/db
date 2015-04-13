<?php

namespace DB\Traits;

trait Joins {

	protected $join = '';

	public function join($table, $left, $op, $right, $type = 'INNER') {
		$this->join .= sprintf(' %s JOIN %s ON(%s %s %s) ', $type, $this->wrap($table), $this->column($left), $op, $this->column($right));

		return $this;
	}

	public function leftJoin($table, $left, $op, $right) {
		return $this->join($table, $left, $op, $right, 'LEFT');
	}

}
