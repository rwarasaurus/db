<?php

namespace DB;

use PDO;
use PDOException;

use DB\Query\Expression;
use DB\Query\Builder;
use DB\Query\BuilderInterface;

class Query implements QueryInterface {

	protected $pdo;

	protected $prototype;

	protected $result;

	protected $grammar;

	protected $profile = [];

	protected $profiling = true;

	protected $start;

	public function __construct(PDO $pdo, RowInterface $prototype = null, ResultInterface $result = null, GrammarInterface $grammar = null, BuilderInterface $builder = null) {
		$this->pdo = $pdo;
		$this->prototype = null === $prototype ? new Row : $prototype;
		$this->result = null === $result ? new Result : $result;

		if(null === $grammar) {
			$driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
			$grammar = new Grammar($driver);
		}

		$this->setGrammar($grammar);

		if(null === $builder) {
			$builder = new Builder($this->getGrammar());
		}

		$this->setBuilder($builder);
	}

	public function getGrammar() {
		return $this->grammar;
	}

	public function setGrammar(GrammarInterface $grammar) {
		$this->grammar = $grammar;
	}

	public function getBuilder() {
		return $this->builder;
	}

	public function setBuilder(BuilderInterface $builder) {
		$this->builder = $builder;
	}

	public function __clone() {
		$this->builder = clone $this->builder;
	}

	public function prototype(RowInterface $prototype) {
		$this->prototype = $prototype;

		return $this;
	}

	protected function hydrate(array $row) {
		$obj = clone $this->prototype;

		foreach($row as $key => $value) {
			$obj->$key = $value;
		}

		return $obj;
	}

	public function exec($sql, array $values = [], array $options = []) {
		if($this->profiling) $this->start();

		try {
			$sth = $this->pdo->prepare($sql, $options);
			$result = $sth->execute($values);
		}
		catch(PDOException $e) {
			$error = new SqlException($e->getMessage());
			throw $error->withSql($sql)->withParams($values);
		}

		if($this->profiling) $this->stop($sql, $values, $sth->rowCount());

		$this->reset();

		return (clone $this->result)->withResult($result)->withStatement($sth);
	}

	protected function start() {
		$this->start = microtime(true);
	}

	protected function stop($sql, $values, $rows) {
		$time = microtime(true) - $this->start;
		$this->profile[] = compact('sql', 'values', 'rows', 'time');
	}

	public function getProfile() {
		return $this->profile;
	}

	public function disableProfile() {
		$this->profiling = false;

		return $this;
	}

	public function getLastProfile() {
		return end($this->profile);
	}

	public function getLastSqlString() {
		$row = $this->getLastProfile();

		return $row['sql'];
	}

	public function __call($method, $args) {
		call_user_func_array([$this->builder, $method], $args);

		return $this;
	}

	public function insert(array $values) {
		$this->builder->insert($values);

		$response = $this->exec($this->builder->getSqlString(), $this->builder->getBindings());

		return $response->getResult() ? $this->pdo->lastInsertId() : false;
	}

	public function update(array $values) {
		$this->builder->update($values);

		$response = $this->exec($this->builder->getSqlString(), $this->builder->getBindings());

		return $response->getResult() ? $response->getStatement()->rowCount() : false;
	}

	public function delete() {
		$this->builder->delete();

		$response = $this->exec($this->builder->getSqlString(), $this->builder->getBindings());

		return $response->getResult() ? $response->getStatement()->rowCount() : false;
	}

	public function get() {
		$statement = $this->exec($this->builder->getSqlString(), $this->builder->getBindings())->getStatement();

		$results = [];

		while($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$results[] = $this->hydrate($row);
		}

		$statement->closeCursor();

		return $results;
	}

	public function getCollection() {
		return new Collection($this->get());
	}

	public function fetch() {
		$statement = $this->exec($this->builder->getSqlString(), $this->builder->getBindings())->getStatement();

		$row = $statement->fetch(PDO::FETCH_ASSOC);

		$statement->closeCursor();

		return $row ? $this->hydrate($row) : false;
	}

	public function column($column = 0) {
		$statement = $this->exec($this->builder->getSqlString(), $this->builder->getBindings())->getStatement();

		$value = $statement->fetchColumn($column);

		$statement->closeCursor();

		return $value;
	}

	public function count($column = '*') {
		$func = sprintf('COUNT(%s)', $this->grammar->column($column));

		$this->builder->select([$func]);

		return $this->column();
	}

	public function sum($column) {
		$func = sprintf('SUM(%s)', $this->grammar->column($column));

		$this->builder->select([$func]);

		return $this->column();
	}

	public function incr($column) {
		return $this->modify($column, 1);
	}

	public function decr($column) {
		return $this->modify($column, -1);
	}

	protected function modify(string $column, int $amount): bool {
		return $this->update([
			$column => new Expression(sprintf('%s + %d', $this->grammar->column($column), $amount)),
		]);
	}

}
