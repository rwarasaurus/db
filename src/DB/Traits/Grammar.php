<?php

namespace DB\Traits;

trait Grammar {

	protected $wrapper = '"%s"';

	public function driver($driver) {
		if(false === in_array($driver, $this->supported)) {
			throw new \ErrorException(sprintf('Unsupported database driver: %s', $driver));
		}

		$func = sprintf('get%sWrapFormat', ucfirst($driver));
		$this->wrapper = call_user_func([$this, $func]);

		return $this;
	}

	protected function getMysqlWrapFormat() {
		return '`%s`';
	}

	protected function getSqliteWrapFormat() {
		return '"%s"';
	}

	protected function columns(array $columns) {
		return implode(', ', array_map([$this, 'column'], $columns));
	}

	protected function column($str) {
		if(stripos($str, ' as ') !== false) {
			list($column, $as, $alias) = explode(' ', $str);

			return sprintf('%s %s %s', $this->column($column), strtoupper($as), $this->column($alias));
		}

		return implode('.', array_map([$this, 'wrap'], explode('.', $str)));
	}

	protected function wrap($str) {
		if($str == '*' || strpos($str, '(') !== false || strpos($str, ')') !== false) {
			return $str;
		}

		return sprintf($this->wrapper, $str);
	}

	protected function placeholders(array $items) {
		return implode(', ', array_fill(0, count($items), '?'));
	}

}
