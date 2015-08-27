<?php

namespace DB;

use PDO;
use PDOException;

class Query {

	protected $pdo;

	protected $prototype;

	protected $result;

	protected $grammar;

	protected $profile = [];

	protected $profiling = true;

	protected $start;

	protected $table;

	protected $select = '*';

	protected $values = [];

	protected $groups = [];

	protected $sorts = [];

	protected $limit;

	protected $offset;

	protected $append_condition = false;

	protected $where = [];

	protected $join = [];

	public function __construct(PDO $pdo, RowInterface $prototype = null, ResultInterface $result = null, GrammarInterface $grammar = null) {
		$this->pdo = $pdo;
		$this->prototype = null === $prototype ? new Row : $prototype;
		$this->result = null === $result ? new Result : $result;

		if(null === $grammar) {
			$driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
			$grammar = new Grammar($driver);
		}

		$this->grammar = $grammar;
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

		$return = clone $this->result;

		return $return->withResult($result)->withStatement($sth);
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
		return $this->getLastProfile()['sql'];
	}

	public function select(array $columns) {
		$this->select = $this->grammar->columns($columns);

		return $this;
	}

	public function table($table) {
		$this->table = $this->grammar->wrap($table);

		return $this;
	}

	public function group($column) {
		$this->groups[] = $this->grammar->column($column);

		return $this;
	}

	public function sort($column, $mode = 'ASC') {
		$this->sorts[] = sprintf('%s %s', $this->grammar->column($column), strtoupper($mode));

		return $this;
	}

	public function take($perpage) {
		$this->limit = (int) $perpage;

		return $this;
	}

	public function skip($offset) {
		$this->offset = (int) $offset;

		return $this;
	}

	public function reset() {
		$this->select = '*';
		$this->table = null;
		$this->where = [];
		$this->join = [];
		$this->groups = [];
		$this->sorts = [];
		$this->values = [];
		$this->limit = null;
		$this->offset = null;
		$this->append_condition = false;

		return $this;
	}

	public function getSqlString() {
		$sql = 'SELECT '.$this->select;

		if($this->table) {
			$sql .= ' FROM '.$this->table;
		}

		if(count($this->join)) {
			$sql .= ' '.implode(' ', $this->join);
		}

		if(count($this->where)) {
			$sql .= ' WHERE '.implode(' ', $this->where);
		}

		if(count($this->groups)) {
			$sql .= ' GROUP BY '.implode(', ', $this->groups);
		}

		if(count($this->sorts)) {
			$sql .= ' ORDER BY '.implode(', ', $this->sorts);
		}

		if($this->limit) {
			$sql .= ' LIMIT '.$this->limit;

			if($this->offset) {
				$sql .= ' OFFSET '.$this->offset;
			}
		}

		return $sql;
	}

	public function getBindings() {
		return $this->values;
	}

	protected function nest() {
		$this->append_condition = false;
		$this->where[] = '(';
	}

	protected function unnest() {
		$this->append_condition = true;
		$this->where[] = ')';
	}

	public function where($key, $op = null, $value = null, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		if($key instanceof \Closure) {
			$this->nest();
			$key($this);
			$this->unnest();

			return $this;
		}

		$this->where[] = sprintf('%s %s ?', $this->grammar->column($key), $op);
		$this->values[] = $value;

		$this->append_condition = true;

		return $this;
	}

	public function orWhere($key, $op = null, $value = null) {
		return $this->where($key, $op, $value, 'OR');
	}

	public function whereRaw($sql, array $values = [], $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = $sql;
		$this->values = array_merge($this->values, $values);

		$this->append_condition = true;

		return $this;
	}

	public function orWhereRaw($sql, array $values = []) {
		return $this->whereRaw($sql, $values, 'OR');
	}

	public function whereIsNull($key, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = sprintf('%s IS NULL', $this->grammar->column($key));

		$this->append_condition = true;

		return $this;
	}

	public function orWhereIsNull($key) {
		return $this->whereIsNull($key, 'OR');
	}

	public function whereIsNotNull($key, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = sprintf('%s IS NOT NULL', $this->grammar->column($key));

		$this->append_condition = true;

		return $this;
	}

	public function orWhereIsNotNull($key) {
		return $this->whereIsNotNull($key, 'OR');
	}

	public function whereIn($key, array $values, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = sprintf('%s IN(%s)', $this->grammar->column($key), $this->grammar->placeholders($values));
		$this->values = array_merge($this->values, $values);

		$this->append_condition = true;

		return $this;
	}

	public function orWhereIn($key, array $values) {
		return $this->whereIn($key, $values, 'OR');
	}

	public function whereNotIn($key, array $values, $condition = 'AND') {
		if($this->append_condition) $this->where[] = $condition;

		$this->where[] = sprintf('%s NOT IN(%s)', $this->grammar->column($key), $this->grammar->placeholders($values));
		$this->values = array_merge($this->values, $values);

		$this->append_condition = true;

		return $this;
	}

	public function orWhereNotIn($key, array $values) {
		return $this->whereNotIn($key, $values, 'OR');
	}

	public function join($table, $left, $op, $right, $type = 'INNER') {
		$this->join[] = sprintf('%s JOIN %s ON(%s %s %s)',
			$type,
			$this->grammar->wrap($table),
			$this->grammar->column($left),
			$op,
			$this->grammar->column($right)
		);

		return $this;
	}

	public function leftJoin($table, $left, $op, $right) {
		return $this->join($table, $left, $op, $right, 'LEFT');
	}

	public function joinRaw($sql) {
		$this->join[] = $sql;

		return $this;
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
