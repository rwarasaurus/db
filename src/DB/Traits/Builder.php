<?php

namespace DB\Traits;

trait Builder {

	use Wheres, Joins;

	protected $table;

	protected $select = '*';

	protected $values = [];

	protected $groups = [];

	protected $sorts = [];

	protected $limit;

	protected $offset;

	public function select(array $columns) {
		$this->select = $this->grammar->columns($columns);

		return $this;
	}

	public function table($table) {
		$this->table = $this->grammar->wrap($table);

		return $this;
	}

	public function group($column) {
		$this->groups[] = $this->grammar->column($column);

		return $this;
	}

	public function sort($column, $mode = 'ASC') {
		$this->sorts[] = sprintf('%s %s', $this->grammar->column($column), strtoupper($mode));

		return $this;
	}

	public function take($perpage) {
		$this->limit = (int) $perpage;

		return $this;
	}

	public function skip($offset) {
		$this->offset = (int) $offset;

		return $this;
	}

	public function reset() {
		$this->select = '*';
		$this->table = null;
		$this->where = [];
		$this->join = [];
		$this->groups = [];
		$this->sorts = [];
		$this->values = [];
		$this->limit = null;
		$this->offset = null;
		$this->append_where_join = false;

		return $this;
	}

	public function getSqlString() {
		$sql = 'SELECT '.$this->select;

		if($this->table) {
			$sql .= ' FROM '.$this->table;
		}

		if(count($this->join)) {
			$sql .= ' '.implode(' ', $this->join);
		}

		if(count($this->where)) {
			$sql .= ' WHERE '.implode(' ', $this->where);
		}

		if(count($this->groups)) {
			$sql .= ' GROUP BY '.implode(', ', $this->groups);
		}

		if(count($this->sorts)) {
			$sql .= ' ORDER BY '.implode(', ', $this->sorts);
		}

		if($this->limit) {
			$sql .= ' LIMIT '.$this->limit;

			if($this->offset) {
				$sql .= ' OFFSET '.$this->offset;
			}
		}

		return $sql;
	}

	public function getBindings() {
		return $this->values;
	}

}
