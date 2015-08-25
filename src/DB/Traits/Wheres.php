<?php

namespace DB\Traits;

trait Wheres {

	/**
	 * Conditions should only be appended inbetween statements
	 *
	 * @var bool
	 */
	protected $append_condition = false;

	/**
	 * The where array
	 *
	 * @var string
	 */
	protected $where = [];

	protected function nest() {
		$this->append_condition = false;
		$this->where[] = '(';
	}

	protected function unnest() {
		$this->append_condition = true;
		$this->where[] = ')';
	}

	public function where($key, $op = null, $value = null, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		if($key instanceof \Closure) {
			$this->nest();
			$key($this);
			$this->unnest();

			return $this;
		}

		$this->where[] = sprintf('%s %s ?', $this->grammar->column($key), $op);
		$this->values[] = $value;

		$this->append_condition = true;

		return $this;
	}

	public function orWhere($key, $op = null, $value = null) {
		return $this->where($key, $op, $value, 'OR');
	}

	public function whereRaw($sql, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = $sql;

		$this->append_condition = true;

		return $this;
	}

	public function orWhereRaw($sql) {
		return $this->whereRaw($sql, 'OR');
	}

	public function whereIsNull($key, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = sprintf('%s IS NULL', $this->grammar->column($key));

		$this->append_condition = true;

		return $this;
	}

	public function orWhereIsNull($key) {
		return $this->whereIsNull($key, 'OR');
	}

	public function whereIsNotNull($key, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = sprintf('%s IS NOT NULL', $this->grammar->column($key));

		$this->append_condition = true;

		return $this;
	}

	public function orWhereIsNotNull($key) {
		return $this->whereIsNotNull($key, 'OR');
	}

	public function whereIn($key, array $values, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = sprintf('%s IN(%s)', $this->grammar->column($key), $this->grammar->placeholders($values));
		$this->values = array_merge($this->values, $values);

		$this->append_condition = true;

		return $this;
	}

	public function orWhereIn($key, array $values) {
		return $this->whereIn($key, $values, 'OR');
	}

	public function whereNotIn($key, array $values, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = sprintf('%s NOT IN(%s)', $this->grammar->column($key), $this->grammar->placeholders($values));
		$this->values = array_merge($this->values, $values);

		$this->append_condition = true;

		return $this;
	}

	public function orWhereNotIn($key, array $values) {
		return $this->whereNotIn($key, $values, 'OR');
	}

}
