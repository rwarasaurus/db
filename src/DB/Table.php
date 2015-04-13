<?php

namespace DB;

class Table {

	/**
	 * Table Name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Table Primary Key
	 *
	 * @var string
	 */
	protected $primary;

	/**
	 * Query Builder
	 *
	 * @var object
	 */
	protected $query;

	/**
	 * Model Prototype
	 *
	 * @var object
	 */
	protected $prototype;

	/**
	 * Table Gateway constructor
	 *
	 * @param object
	 * @param object
	 * @param string
	 * @param string
	 */
	public function __construct(Query $query, Row $prototype, $name, $primary = 'id') {
		$this->name = $name;
		$this->primary = $primary;
		$this->query = $query;
		$this->prototype = $prototype;
	}

	/**
	 * Call methods on the Query Builder
	 *
	 * @param string
	 * @param array
	 * @return object
	 */
	public function __call($method, array $args) {
		return call_user_func_array([$this->query->table($this->name)->hydrate($this->prototype), $method], $args);
	}

	/**
	 * Save a Active Record to the DB
	 *
	 * @param object
	 * @return bool
	 */
	public function save(Row $row) {
		$data = $row->toArray();

		if(false === array_key_exists($this->primary, $data)) {
			$row->{$this->primary} = $this->query->table($this->name)->insert($data);
		}
		else {
			$this->query->table($this->name)->where($this->primary, '=', $row->{$this->primary})->update($data);
		}

		return true;
	}
}
