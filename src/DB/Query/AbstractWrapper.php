<?php

namespace DB\Query;

abstract class AbstractWrapper {

	protected function wrap($value) {
		// if null use the literal value
		if(null === $value) {
			return 'NULL';
		}

		// if expression use as literal value
		if($value instanceof Expression) {
			return (string) $value;
		}

		// if its a subquery
		if($value instanceof BuilderInterface) {
			$this->bindings = array_merge($this->bindings, $value->getBindings());
			if($alias = $value->getAlias()) {
				return sprintf('(%s) AS %s', $value->getSqlString(), $this->grammar->wrap($alias));
			}
			return sprintf('(%s)', $value->getSqlString());
		}

		// if the value is an array treat it as raw values
		if(is_array($value)) {
			$this->bindings = array_merge($this->bindings, $value);
			return sprintf('(%s)', $this->grammar->placeholders($value));
		}

		// if we find a dot notation thats not the first character we treat
		// it as a column definition
		if(is_string($value) && preg_match('#^[A-z0-9-_]+(\.[A-z0-9-_]+)?$#', $value)) {
			return $this->grammar->column($value);
		}

		// otherwise we treat it as a single raw value
		$this->bindings[] = $value;
		return '?';
	}

}
