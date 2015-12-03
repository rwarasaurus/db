<?php

namespace DB;

interface QueryInterface {

	public function exec($sql, array $values = [], array $options = []);

	public function getProfile();

	public function disableProfile();

	public function getLastProfile();

	public function getLastSqlString();

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
