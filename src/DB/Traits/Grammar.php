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
		// handle alias
		if(strpos($str, ' AS ')) {
			return $this->alias($str);
		}

		return $this->wrap($str);
	}

	protected function alias($str) {
		list($column, $alias) = explode(' AS ', $str);

		return sprintf('%s AS %s', $this->wrap($column), $this->wrap($alias));
	}

	protected function wrap($str) {
		if(preg_match('#(\*|\(|\)|\+|\-|\s)#', $str)) {
			return $str;
		}

		$fragments = explode('.', $str);

		$formatted = array_map(function($str) { return sprintf($this->wrapper, $str); }, $fragments);

		return implode('.', $formatted);
	}

	protected function placeholders(array $items) {
		return implode(', ', array_fill(0, count($items), '?'));
	}

}
