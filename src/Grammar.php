<?php

namespace DB;

class Grammar implements GrammarInterface {

	protected $aliasPattern = ' AS ';

	protected $wrapper = '"%s"';

	protected $supported = [
		'mysql' => '`%s`',
		'pgsql' => '"%s"',
		'sqlite' => '"%s"',
	];

	public function __construct($driver) {
		if(false === array_key_exists($driver, $this->supported)) {
			throw new \ErrorException(sprintf('Unsupported database driver: %s', $driver));
		}

		$this->wrapper = $this->supported[$driver];
	}

	public function columns(array $columns) {
		return implode(', ', array_map([$this, 'column'], $columns));
	}

	public function column($str) {
		// handle alias
		if(strpos($str, $this->aliasPattern)) {
			return $this->alias($str);
		}

		return $this->wrap($str);
	}

	protected function alias($str) {
		list($column, $alias) = explode($this->aliasPattern, $str);

		return $this->wrap($column) . $this->aliasPattern . $this->wrap($alias);
	}

	public function wrap($str) {
		if( ! is_string($str)) {
			throw new \InvalidArgumentException(sprintf('Argument should be a string, %s given.', gettype($str)));
		}

		// dont wrap expressions
		if(preg_match('#(\*|\(|\)|\+|\-|\s)#', $str)) {
			return $str;
		}

		$fragments = explode('.', $str);

		$formatted = array_map(function($str) {
			return sprintf($this->wrapper, $str);
		}, $fragments);

		return implode('.', $formatted);
	}

	public function placeholders(array $items) {
		return implode(', ', array_fill(0, count($items), '?'));
	}

}
