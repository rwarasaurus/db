<?php

namespace DB\Query;

abstract class AbstractWrapper {

	protected function wrap($value) {
		// if its a subquery
		if($value instanceof BuilderInterface) {
			$this->bindings = array_merge($this->bindings, $value->getBindings());
			return sprintf('(%s)', $value->getSqlString());
		}

		// if the value is an array treat it as raw values
		if(is_array($value)) {
			$this->bindings = array_merge($this->bindings, $value);
			return sprintf('(%s)', $this->grammer->placeholders($value));
		}

		// if we find a dot notation thats not the first character we treat
		// it as a column definition
		if(is_string($value) && strpos($value, '.')) {
			return $this->grammer->column($value);
		}

		// otherwise we treat it as a single raw value
		$this->bindings[] = $value;
		return '?';
	}

}
