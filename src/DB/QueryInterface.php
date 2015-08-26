<?php

namespace DB;

interface QueryInterface {

	public function exec($sql, array $values = [], array $options = []);

	public function getProfile();

	public function disableProfile();

	public function getLastProfile();

	public function getLastSqlString();

	public function select(array $columns);

	public function table($table);

	public function group($column);

	public function sort($column, $mode = 'ASC');

	public function take($perpage);

	public function skip($offset);

	public function reset();

	public function getSqlString();

	public function getBindings();

	public function where($key, $op = null, $value = null, $condition = 'AND');

	public function orWhere($key, $op = null, $value = null);

	public function whereRaw($sql, $condition = 'AND');

	public function orWhereRaw($sql);

	public function whereIsNull($key, $condition = 'AND');

	public function orWhereIsNull($key);

	public function whereIsNotNull($key, $condition = 'AND');

	public function orWhereIsNotNull($key);

	public function whereIn($key, array $values, $condition = 'AND');

	public function orWhereIn($key, array $values);

	public function whereNotIn($key, array $values, $condition = 'AND');

	public function orWhereNotIn($key, array $values);

	public function join($table, $left, $op, $right, $type = 'INNER');

	public function leftJoin($table, $left, $op, $right);

	public function joinRaw($sql);

	public function update(array $fields);

	public function insert(array $data);

	public function delete();

	public function get();

	public function fetch();

	public function column($column = 0);

	public function count($column = '*');

	public function sum($column);

	public function incr($column);

	public function decr($column);

}
