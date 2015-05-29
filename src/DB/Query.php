<?php

namespace DB;

use PDO;

use DB\Contracts\Row as RowInterface;
use DB\Traits\Builder as BuilderTrait;
use DB\Traits\Profile as ProfileTrait;

class Query {

	use BuilderTrait, ProfileTrait;

	protected $pdo;

	protected $prototype;

	protected $supported = ['mysql', 'sqlite'];

	public function __construct(PDO $pdo, RowInterface $prototype = null) {
		$this->pdo = $pdo;
		$this->prototype = null === $prototype ? new Row : $prototype;
	}

	public function exec($sql, array $values = []) {
		try {
			if($this->profiling) {
				$this->start();
			}

			$sth = $this->pdo->prepare($sql);
			$sth->execute($values);

			if($this->profiling) {
				$this->stop($sql, $values, $sth->rowCount());
			}
		}
		catch(\Exception $e) {
			$error = new SqlSyntaxException($e->getMessage());
			throw $error->withSql($sql)->withParams($values);
		}

		$this->reset();

		return $sth;
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
		$sth = $this->exec($this->getSqlString(), $this->getBindings());
		$results = [];

		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$results[] = $this->hydrate($row);
		}

		$sth->closeCursor();

		return $results;
	}

	public function fetch() {
		$results = $this->get();

		return isset($results[0]) ? $results[0] : false;
	}

	public function col($column = 0) {
		return $this->exec($this->getSqlString(), $this->values)->fetchColumn();
	}

	public function count($column = '*') {
		$func = sprintf('COUNT(%s)', $this->column($column));
		return $this->select([$func])->exec($this->getSqlString(), $this->values)->fetchColumn();
	}

	public function sum($column) {
		$func = sprintf('SUM(%s)', $this->column($column));
		return $this->select([$func])->exec($this->getSqlString(), $this->values)->fetchColumn();
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

		return $this->exec($sql, $this->values)->rowCount();
	}

	public function insert(array $data) {
		$columns = $this->columns(array_keys($data));
		$values = array_values($data);

		$sql = sprintf('INSERT INTO %s (%s) VALUES(%s)', $this->table, $columns, $this->placeholders($data));
		$this->exec($sql, $values);

		return $this->pdo->lastInsertId();
	}

	public function delete() {
		$sql = sprintf('DELETE FROM %s', $this->table);

		if($this->where) {
			$sql .= ' WHERE '.$this->where;
		}

		return $this->exec($sql, $this->values)->rowCount();
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

		return $this->exec($sql, $this->values)->rowCount();
	}

}
