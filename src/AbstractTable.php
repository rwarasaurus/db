<?php

namespace DB;

abstract class AbstractTable implements TableInterface {

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
	public function __construct(Query $query, RowInterface $prototype) {
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
		return call_user_func_array([$this->query(), $method], $args);
	}

	/**
	 * Start a new query on the Query Builder
	 *
	 * @return object
	 */
	public function query() {
		return $this->query->table($this->name)->prototype($this->prototype);
	}

	/**
	 * Save a Active Record to the DB
	 *
	 * @param object
	 * @return bool
	 */
	public function save(RowInterface $row) {
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
