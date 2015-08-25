<?php

namespace DB;

use PDOStatement;

class ResultInterface {

	public function getResult();

	public function withResult($result);

	public function getStatement();

	public function withStatement(PDOStatement $statement);

}
