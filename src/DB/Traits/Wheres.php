<?php

namespace DB\Traits;

trait Wheres {

	protected $append_where_join = false;

	protected $where = '';

	protected function nest() {
		$this->append_where_join = false;
		$this->where .= ' ( ';
	}

	protected function unnest() {
		$this->append_where_join = true;
		$this->where .= ' ) ';
	}

	public function where($key, $op = null, $value = null, $join = 'AND') {
		if($this->append_where_join) {
			$this->where .= $join;
		}

		if($key instanceof \Closure) {
			$this->nest();
			$key($this);
			$this->unnest();

			return $this;
		}

		$this->where .= sprintf(' %s %s ? ', $this->column($key), $op);
		$this->values[] = $value;

		$this->append_where_join = true;

		return $this;
	}

	public function orWhere($key, $op = null, $value = null) {
		return $this->where($key, $op, $value, 'OR');
	}

	public function whereIsNull($key, $join = 'AND') {
		if($this->append_where_join) {
			$this->where .= $join;
		}

		$this->where .= sprintf(' %s IS NULL ', $this->column($key));

		$this->append_where_join = true;

		return $this;
	}

	public function orWhereIsNull($key) {
		return $this->whereIsNull($key, 'OR');
	}

	public function whereIsNotNull($key, $join = 'AND') {
		if($this->append_where_join) {
			$this->where .= $join;
		}

		$this->where .= sprintf(' %s IS NOT NULL ', $this->column($key));

		$this->append_where_join = true;

		return $this;
	}

	public function orWhereIsNotNull($key) {
		return $this->whereIsNotNull($key, 'OR');
	}

	public function whereIn($key, array $values, $join = 'AND') {
		if($this->append_where_join) {
			$this->where .= $join;
		}

		$this->where .= sprintf(' %s IN(%s) ', $this->column($key), $this->placeholders($values));
		$this->values = array_merge($this->values, $values);

		$this->append_where_join = true;

		return $this;
	}

	public function orWhereIn($key, array $values) {
		return $this->whereIn($key, $values, 'OR');
	}

	public function whereNotIn($key, array $values, $join = 'AND') {
		if($this->append_where_join) {
			$this->where .= $join;
		}

		$this->where .= sprintf(' %s NOT IN(%s) ', $this->column($key), $this->placeholders($values));
		$this->values = array_merge($this->values, $values);

		$this->append_where_join = true;

		return $this;
	}

	public function orWhereNotIn($key, array $values) {
		return $this->whereNotIn($key, $values, 'OR');
	}

}
