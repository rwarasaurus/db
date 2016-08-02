<?php

namespace DB\Query;

use DB\GrammarInterface;

class Table extends AbstractWrapper implements FragmentInterface, BindingsInterface {

	protected $table;

	protected $grammar;

	protected $bindings;

	public function __construct(GrammarInterface $grammar) {
		$this->grammar = $grammar;
		$this->bindings = [];
	}

	public function name(): string {
		if($this->table instanceof BuilderInterface) {
			throw new \RuntimeException('Table name is a query');
		}

		return $this->table;
	}

	public function from(string $table) {
		$this->table = $table;
	}

	public function getSqlString(): string {
		if(null === $this->table) {
			throw new \InvalidArgumentException('Table name has not been set');
		}

		$table = $this->table instanceof BuilderInterface ? $this->wrap($this->table) : $this->grammar->wrap($this->table);

		return sprintf('FROM %s', $table);
	}

	public function getBindings(): array {
		return $this->bindings;
	}

}
