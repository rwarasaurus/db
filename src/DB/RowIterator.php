<?php

class RowIterator implements \Iterator {

	protected $statement;

	protected $key;

	protected $result;

	protected $valid;

	public function __construct(\PDOStatement $statement) {
		$this->statement = $statement;
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
		$this->result = $this->statement->fetch(\PDO::FETCH_ASSOC, \PDO::FETCH_ORI_ABS, $this->key);

		if(false === $this->result) {
			$this->valid = false;

			return null;
		}
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
