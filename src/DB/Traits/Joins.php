<?php

namespace DB\Traits;

trait Joins {

	protected $join = [];

	public function join($table, $left, $op, $right, $type = 'INNER') {
		$this->join[] = sprintf('%s JOIN %s ON(%s %s %s)',
			$type,
			$this->grammar->wrap($table),
			$this->grammar->column($left),
			$op,
			$this->grammar->column($right)
		);

		return $this;
	}

	public function leftJoin($table, $left, $op, $right) {
		return $this->join($table, $left, $op, $right, 'LEFT');
	}

	public function joinRaw($sql) {
		$this->join[] = $sql;

		return $this;
	}

}
