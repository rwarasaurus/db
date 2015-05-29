<?php

namespace DB;

class SqlSyntaxException extends \ErrorException {

	protected $sql;

	protected $params;

	public function getSql() {
		return $this->sql;
	}

	public function withSql($sql) {
		$this->sql = $sql;

		return $this;
	}

	public function getParams() {
		return $this->params;
	}

	public function withParams(array $params) {
		$this->params = $params;

		return $this;
	}

}
