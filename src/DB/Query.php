<?php

namespace DB;

use PDO;
use PDOException;

use DB\Contracts\Row as RowInterface;
use DB\Traits\Builder as BuilderTrait;
use DB\Traits\Profile as ProfileTrait;

class Query {

	use BuilderTrait, ProfileTrait;

	protected $pdo;

	protected $result;

	protected $prototype;

	public function __construct(PDO $pdo, RowInterface $prototype = null, Result $result = null) {
		$this->pdo = $pdo;
		$this->prototype = null === $prototype ? new Row : $prototype;
		$this->result = null === $result ? new Result : $result;
	}

	public function exec($sql, array $values = []) {
		if($this->profiling) {
			$this->start();
		}

		try {
			$sth = $this->pdo->prepare($sql);
			$result = $sth->execute($values);
		}
		catch(PDOException $e) {
			$error = new SqlException($e->getMessage());
			throw $error->withSql($sql)->withParams($values);
		}

		if($this->profiling) {
			$this->stop($sql, $values, $sth->rowCount());
		}

		// reset the builder for next query
		$this->reset();

		$return = clone $this->result;

		return $return->withResult($result)->withStatement($sth);
	}

	public function prototype(RowInterface $prototype) {
		$this->prototype = $prototype;

		return $this;
	}

	public function hydrate(array $row) {
		$obj = clone $this->prototype;

		foreach($row as $key => $value) {
			$obj->$key = $value;
		}

		return $obj;
	}

	public function get() {
		$res = $this->exec($this->getSqlString(), $this->getBindings());
		$sth = $res->getStatement();
		$results = [];

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$results[] = $this->hydrate($row);
		}

		$sth->closeCursor();

		return $results;
	}

	public function fetch() {
		$res = $this->exec($this->getSqlString(), $this->getBindings());

		$row = $res->getStatement()->fetch(PDO::FETCH_ASSOC);

		return is_array($row) ? $this->hydrate($row) : false;
	}

	public function col($column = 0) {
		$res = $this->exec($this->getSqlString(), $this->getBindings());

		return $res->getStatement()->fetchColumn();
	}

	public function count($column = '*') {
		$func = sprintf('COUNT(%s)', $this->column($column));
		$res = $this->select([$func])->exec($this->getSqlString(), $this->values);

		return $res->getStatement()->fetchColumn();
	}

	public function sum($column) {
		$func = sprintf('SUM(%s)', $this->column($column));
		$res = $this->select([$func])->exec($this->getSqlString(), $this->values);

		return $res->getStatement()->fetchColumn();
	}

	public function update(array $fields) {
		$sets = [];
		$values = [];

		foreach($fields as $key => $value) {
			$sets[] =  $this->column($key) . ' = ?';
			$values[] = $value;
		}

		// prepend values before where values
		$this->values = array_merge($values, $this->values);

		$sql = sprintf('UPDATE %s SET %s', $this->table, implode(', ', $sets));

		if($this->where) {
			$sql .= ' WHERE '.$this->where;
		}

		$res = $this->exec($sql, $this->values);

		return $res->getResult() ? $res->getStatement()->rowCount() : false;
	}

	public function insert(array $data) {
		$columns = $this->columns(array_keys($data));
		$values = array_values($data);

		$sql = sprintf('INSERT INTO %s (%s) VALUES(%s)', $this->table, $columns, $this->placeholders($data));
		$res = $this->exec($sql, $values);

		return $res->getResult() ? $this->pdo->lastInsertId() : false;
	}

	public function delete() {
		$sql = sprintf('DELETE FROM %s', $this->table);

		if($this->where) {
			$sql .= ' WHERE '.$this->where;
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
		$sql = sprintf('UPDATE %1$s SET %2$s = %2$s + %3$s', $this->table, $this->column($column), $amount);

		if($this->where) {
			$sql .= ' WHERE '.$this->where;
		}

		$res = $this->exec($sql, $this->values);

		return $res->getResult() ? $res->getStatement()->rowCount() : false;
	}

}
