<?php

namespace DB;

use PDO;
use PDOException;
use DB\Query\Expression;

class Query implements QueryInterface {

	protected $pdo;

	protected $prototype;

	protected $result;

	protected $grammar;

	protected $profile = [];

	protected $profiling = true;

	protected $start;

	protected $cache;

	public function __construct(PDO $pdo, RowInterface $prototype = null, ResultInterface $result = null, GrammarInterface $grammar = null, BuilderInterface $builder = null) {
		$this->pdo = $pdo;
		$this->prototype = null === $prototype ? new Row : $prototype;
		$this->result = null === $result ? new Result : $result;

		if(null === $grammar) {
			$driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
			$grammar = new Grammar($driver);
		}

		$this->grammar = $grammar;
		$this->builder = null === $builder ? new Query\Builder($grammar) : $builder;
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

		return $this->result->withResult($result)->withStatement($sth);
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

	protected function getCacheKey($sql, array $values) {
		return sprintf('cached.select.%s.%s',
			hash('crc32', $sql),
			hash('crc32', implode('', $values))
		);
	}

	protected function getCachedResults($key) {
		$results = $this->cache->get($key);

		return empty($results) ? false : $this->hydrateBufferedResults($results);
	}

	protected function hydrateBufferedResults(array $results) {
		foreach(array_keys($results) as $index) {
			$results[$index] = $this->hydrate($results[$index]);
		}

		return $results;
	}

	protected function setCache($cache) {
		$this->cache = $cache;
	}

	public function get() {
		$sql = $this->builder->getSqlString();
		$values = $this->builder->getBindings();

		if($this->cache) {
			$key = $this->getCacheKey($sql, $values);
			$results = $this->getCachedResults($key);

			if($results) return $results;
		}

		$statement = $this->exec($sql, $values)->getStatement();

		$results = $statement->fetchAll(PDO::FETCH_ASSOC);

		if($this->cache) $this->cache->set($key, $results);

		return $this->hydrateBufferedResults($results);
	}

	public function fetch() {
		$response = $this->exec($this->builder->getSqlString(), $this->builder->getBindings());

		$row = $response->getStatement()->fetch(PDO::FETCH_ASSOC);

		return is_array($row) ? $this->hydrate($row) : false;
	}

	public function column($column = 0) {
		$response = $this->exec($this->builder->getSqlString(), $this->builder->getBindings());

		return $response->getStatement()->fetchColumn($column);
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

	protected function modify($column, $amount) {
		return $this->update([
			$column => new Expression(sprintf('%s + %d', $this->grammar->column($column), $amount))
		]);
	}

}
