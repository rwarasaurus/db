<?php

namespace DB;

use PDOStatement;

class Result {

	protected $result;

	protected $statement;

	public function __construct($result = false, PDOStatement $statement = null) {
		$this->statement = $statement;
		$this->result = $result;
	}

	public function getResult() {
		return $this->result;
	}

	public function withResult($result) {
		$this->result = $result;

		return $this;
	}

	public function getStatement() {
		return $this->statement;
	}

	public function withStatement(PDOStatement $statement) {
		$this->statement = $statement;

		return $this;
	}

}
