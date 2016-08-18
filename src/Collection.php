<?php

namespace DB;

class Collection implements \Iterator, \Countable {

	protected $keys;

	protected $values;

	protected $cursor;

	public function __construct(array $data = []) {
		$this->keys = array_keys($data);
		$this->values = array_values($data);
		$this->rewind();
	}

	public function append($value, $key = null) {
		array_push($this->values, $value);
		array_push($this->keys, (null === $key ? count($this->keys) : $key));
	}

	public function current() {
		return $this->values[$this->cursor];
	}

	public function key() {
		return $this->keys[$this->cursor];
	}

	public function valid() {
		return isset($this->keys[$this->cursor]);
	}

	public function has($key) {
		return in_array($key, $this->keys);
	}

	public function find($key) {
		return $this->reduce(function($carry, $value, $index) use($key) {
			return $index == $key ? $value : $carry;
		});
	}

	public function next() {
		$this->cursor += 1;
	}

	public function rewind() {
		$this->cursor = 0;
	}

	public function count() {
		return count($this->keys);
	}

	public function map(callable $callback): self {
		$newCollection = new static;
		foreach($this->toArray() as $key => $value) {
			$newCollection->append($callback($value, $key), $key);
		}
		return $newCollection;
	}

	public function filter(callable $callback): self {
		$newCollection = new static;
		foreach($this->toArray() as $key => $value) {
			if($callback($value, $key)) {
				$newCollection->append($value, $key);
			}
		}
		return $newCollection;
	}

	public function reduce(callable $callback) {
		$carry = null;
		foreach($this->toArray() as $key => $value) {
			$carry = $callback($carry, $value, $key);
		}
		return $carry;
	}

	public function extract(array $keys): self {
		return $this->filter(function($value, $key) use($keys) {
			return in_array($key, $keys);
		});
	}

	public function reindex(callable $callback): self {
		$newCollection = new static;
		foreach($this->toArray() as $key => $value) {
			$newCollection->append($value, $callback($value, $key));
		}
		return $newCollection;
	}

	public function toArray(): array {
		return array_combine($this->keys, $this->values);
	}

}
