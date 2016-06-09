<?php

namespace DB\Query\BuilderTraits;

trait Where {

	protected $where;

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

	public function orWhereIn($column, array $values) {
		$this->where->orIn($column, $values);

		return $this;
	}

	public function whereNotIn($column, array $values) {
		$this->where->notIn($column, $values);

		return $this;
	}

	public function orWhereNotIn($column, array $values) {
		$this->where->orNotIn($column, $values);

		return $this;
	}

	public function whereInQuery($column, \Closure $predicate) {
		$this->where->inQuery($column, $this->subQuery($predicate));

		return $this;
	}

	public function orWhereInQuery($column, \Closure $predicate) {
		$this->where->orInQuery($column, $this->subQuery($predicate));

		return $this;
	}

	public function whereNotInQuery($column, \Closure $predicate) {
		$this->where->notInQuery($column, $this->subQuery($predicate));

		return $this;
	}

	public function orWhereNotInQuery($column, \Closure $predicate) {
		$this->where->orNotInQuery($column, $this->subQuery($predicate));

		return $this;
	}

	public function whereIsNull($column) {
		$this->where->isNull($column);

		return $this;
	}

	public function orWhereIsNull($column) {
		$this->where->orIsNull($column);

		return $this;
	}

	public function whereIsNotNull($column) {
		$this->where->isNotNull($column);

		return $this;
	}

	public function orWhereIsNotNull($column) {
		$this->where->orIsNotNull($column);

		return $this;
	}

}
