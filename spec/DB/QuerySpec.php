<?php

namespace spec\DB;

use PhpSpec\ObjectBehavior;

class QuerySpec extends ObjectBehavior {

	public function Let() {
		$pdo = new \PDO('sqlite::memory:');
		$this->beConstructedWith($pdo);
		$this->shouldBeAnInstanceOf('Query');
	}

}
