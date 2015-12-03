<?php

namespace DB\Query;

use DB\GrammarInterface;

class Select implements FragmentInterface {

	protected $columns;

	protected $grammer;

	public function __construct(array $columns, GrammarInterface $grammer) {
		$this->columns = $columns;
		$this->grammer = $grammer;
	}

	public function getSqlString() {
		return sprintf('SELECT %s', $this->grammer->columns($this->columns));
	}

}
