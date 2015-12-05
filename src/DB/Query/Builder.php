<?php

namespace DB\Query;

use DB\GrammarInterface;

class Builder implements BuilderInterface {

	use BuilderTraits\Where, BuilderTraits\Join;

	protected $grammar;

	protected $insert;

	protected $update;

	protected $delete;

	protected $select;

	protected $table;

	protected $group;

	protected $sort;

	protected $limit;

	protected $offset;

	protected $alias;

	public function __construct(GrammarInterface $grammar) {
		$this->grammar = $grammar;
		$this->reset();
	}

	public function __clone() {
		$this->select = clone $this->select;
		$this->table = clone $this->table;
		$this->where = clone $this->where;
		$this->group = clone $this->group;
		$this->sort = clone $this->sort;
	}

	public function reset() {
		$this->insert = null;
		$this->update = null;
		$this->delete = null;
		$this->select = new Select($this->grammar);
		$this->table = new Table($this->grammar);
		$this->where = new Where($this->grammar);
		$this->joins = [];
		$this->group = new Group($this->grammar);
		$this->sort = new Sort($this->grammar);
		$this->limit = null;
		$this->offset = null;
		$this->alias = null;

		return $this;
	}

	protected function subQuery(\Closure $predicate, $alias = null) {
		$query = new static($this->grammar);

		$predicate($query);

		if($alias) $query->setAlias($alias);

		return $query;
	}

	public function setAlias($alias) {
		$this->alias = $alias;
	}

	public function getAlias() {
		return $this->alias;
	}

	public function select(array $columns) {
		$this->select->columns($columns);

		return $this;
	}

	public function table($table) {
		$this->table->from($table);

		return $this;
	}

	public function tableQuery(\Closure $predicate, $alias) {
		$this->table->from($this->subQuery($predicate, $alias));

		return $this;
	}

	public function group($column) {
		$this->group->by($column);

		return $this;
	}

	public function sort($column, $mode = 'asc') {
		$this->sort->by($column, $mode);

		return $this;
	}

	public function sortField($field, array $keys) {
		$this->sort->field($field, $keys);

		return $this;
	}

	public function take($limit) {
		$this->limit = $limit;

		return $this;
	}

	public function skip($offset) {
		$this->offset = $offset;

		return $this;
	}

	public function insert(array $values) {
		$this->insert = new Insert($this->table->name(), $values, $this->grammar);

		return $this;
	}

	public function update(array $values) {
		$this->update = new Update($this->table->name(), $values, $this->grammar);

		return $this;
	}

	public function delete() {
		$this->delete = new Delete($this->table->name(), $this->grammar);

		return $this;
	}

	public function getSqlString() {
		$fragments = [];

		if($this->insert) {
			$fragments[] = $this->insert->getSqlString();
		}
		else if($this->update) {
			$fragments[] = $this->update->getSqlString();

			$fragments[] = $this->where->getSqlString();
		}
		else if($this->delete) {
			$fragments[] = $this->delete->getSqlString();

			$fragments[] = $this->where->getSqlString();
		}
		else {
			$fragments[] = $this->select->getSqlString();

			$fragments[] = $this->table->getSqlString();

			foreach($this->joins as $fragment) {
				$fragments[] = $fragment->getSqlString();
			}

			$fragments[] = $this->where->getSqlString();

			$fragments[] = $this->group->getSqlString();

			$fragments[] = $this->sort->getSqlString();

			if($this->limit) {
				$fragments[] = sprintf('LIMIT %d', $this->limit);
			}

			if($this->offset) {
				$fragments[] = sprintf('OFFSET %d', $this->offset);
			}

		}

		return implode(' ', array_filter($fragments));
	}

	public function getBindings() {
		if($this->insert) {
			$bindings = $this->insert->getBindings();
		}
		else if($this->update) {
			$bindings = $this->update->getBindings();
		}
		else {
			$bindings = [];

			foreach($this->joins as $fragment) {
				$bindings = array_merge($bindings, $fragment->getBindings());
			}
		}

		$bindings = array_merge($bindings, $this->where->getBindings());

		return $bindings;
	}

}
