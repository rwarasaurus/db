<?php

namespace DB;

use PDO;
use PDOStatement;
use DB\Traits\PrototypeHydrator as PrototypeHydrator;

class RowIterator implements \Iterator {

	use PrototypeHydrator;

	protected $statement;

	protected $key;

	protected $result;

	protected $valid;

	public function __construct(PDOStatement $statement, RowInterface $prototype = null) {
		$this->statement = $statement;
		$this->prototype = null === $prototype ? new Row : $prototype;
	}

	/**
	 * @inheritDoc
	 */
	public function current() {
		return $this->result;
	}

	/**
	 * @inheritDoc
	 */
	public function next() {
		$this->key++;

		$row = $this->statement->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $this->key);

		if(false === $row) {
			$this->valid = false;

			return null;
		}

		$this->result = $this->hydrate($row);
	}

	/**
	 * @inheritDoc
	 */
	public function key() {
		return $this->key;
	}

	/**
	 * @inheritDoc
	 */
	public function valid() {
		return $this->valid;
	}

	/**
	 * @inheritDoc
	 */
	public function rewind() {
		$this->key = 0;
	}

}
