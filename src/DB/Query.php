<?php

namespace DB;

use PDO;
use PDOException;

use DB\Traits\Builder as BuilderTrait;
use DB\Traits\Profile as ProfileTrait;
use DB\Traits\PrototypeHydrator as PrototypeHydratorTrait;

class Query {

	use BuilderTrait, ProfileTrait, PrototypeHydratorTrait;

	protected $pdo;

	protected $result;

	protected $grammar;

	public function __construct(PDO $pdo, RowInterface $prototype = null, ResultInterface $result = null, GrammarInterface $grammar = null) {
		$this->pdo = $pdo;
		$this->prototype = null === $prototype ? new Row : $prototype;
		$this->result = null === $result ? new Result : $result;
		$this->grammar = null === $grammar ? new Grammar($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME)) : $grammar;
	}

	public function exec($sql, array $values = [], array $options = []) {
		$this->start();

		try {
			$sth = $this->pdo->prepare($sql, $options);

			if(false === $sth) {
				// was it a unsupported driver option?
				if($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite' &&
					array_key_exists(PDO::ATTR_CURSOR, $options) &&
					$options[PDO::ATTR_CURSOR] === PDO::CURSOR_SCROLL) {

					throw new \InvalidArgumentException('sqlite does not support the PDO::CURSOR_SCROLL attribute.');
				}

				throw new \RuntimeException('failed to prepare statement.');
			}

			$result = $sth->execute($values);
		}
		catch(PDOException $e) {
			$error = new SqlException($e->getMessage());
			throw $error->withSql($sql)->withParams($values);
		}

		$this->stop($sql, $values, $sth->rowCount());

		// reset the builder for next query
		$this->reset();

		$return = clone $this->result;

		return $return->withResult($result)->withStatement($sth);
	}

	public function get($buffered = true) {
		$options = $buffered ? [] : [PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL];
		$res = $this->exec($this->getSqlString(), $this->getBindings(), $options);
		$sth = $res->getStatement();

		if($buffered) {
			$results = [];

			while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
				$results[] = $this->hydrate($row);
			}

			$sth->closeCursor();

			return $results;
		}

		$iterator = new RowIterator($sth);

		$iterator->prototype($this->prototype);

		return $iterator;
	}

	public function fetch() {
		$res = $this->exec($this->getSqlString(), $this->getBindings());

		$row = $res->getStatement()->fetch(PDO::FETCH_ASSOC);

		return is_array($row) ? $this->hydrate($row) : false;
	}

	public function column($column = 0) {
		$res = $this->exec($this->getSqlString(), $this->getBindings());

		return $res->getStatement()->fetchColumn();
	}

	public function count($column = '*') {
		$func = sprintf('COUNT(%s)', $this->grammar->column($column));
		$res = $this->select([$func])->exec($this->getSqlString(), $this->values);

		return $res->getStatement()->fetchColumn();
	}

	public function sum($column) {
		$func = sprintf('SUM(%s)', $this->grammar->column($column));
		$res = $this->select([$func])->exec($this->getSqlString(), $this->values);

		return $res->getStatement()->fetchColumn();
	}

	public function update(array $fields) {
		$sets = [];
		$values = [];

		foreach($fields as $key => $value) {
			$sets[] =  $this->grammar->column($key) . ' = ?';
			$values[] = $value;
		}

		// prepend values before where values
		$this->values = array_merge($values, $this->values);

		$sql = sprintf('UPDATE %s SET %s', $this->table, implode(', ', $sets));

		if(count($this->where)) {
			$sql .= ' WHERE '.implode(' ', $this->where);
		}

		$res = $this->exec($sql, $this->values);

		return $res->getResult() ? $res->getStatement()->rowCount() : false;
	}

	public function insert(array $data) {
		$columns = $this->grammar->columns(array_keys($data));
		$values = array_values($data);

		$sql = sprintf('INSERT INTO %s (%s) VALUES(%s)', $this->table, $columns, $this->grammar->placeholders($data));
		$res = $this->exec($sql, $values);

		return $res->getResult() ? $this->pdo->lastInsertId() : false;
	}

	public function delete() {
		$sql = sprintf('DELETE FROM %s', $this->table);

		if(count($this->where)) {
			$sql .= ' WHERE '.implode(' ', $this->where);
		}

		$res = $this->exec($sql, $this->values);

		return $res->getResult() ? $res->getStatement()->rowCount() : false;
	}

	public function incr($column) {
		return $this->modify($column, 1);
	}

	public function decr($column) {
		return $this->modify($column, -1);
	}

	protected function modify($column, $amount) {
		$sql = sprintf('UPDATE %1$s SET %2$s = %2$s + %3$s', $this->table, $this->grammar->column($column), $amount);

		if(count($this->where)) {
			$sql .= ' WHERE '.implode(' ', $this->where);
		}

		$res = $this->exec($sql, $this->values);

		return $res->getResult() ? $res->getStatement()->rowCount() : false;
	}

}
