<?php

use DB\Query;

class WheresTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->pdo = new PDO('sqlite::memory:');
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		$this->pdo->exec('PRAGMA synchronous = OFF');
		$this->pdo->exec('PRAGMA journal_mode = OFF');

		$this->pdo->exec('CREATE TABLE [books] ([id] integer PRIMARY KEY AUTOINCREMENT, [title] text, [author] integer)');
	}

	protected function tearDown() {
		$this->pdo = null;
	}

	public function testWhere()  {
		$q = new Query($this->pdo);

		$q->table('books')->where('id', '=', 1);
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" = ? ', $q->getSqlString());
	}

	public function testWhereNested()  {
		$q = new Query($this->pdo);

		$q->table('books')->where(function($q) {
			$q->where('id', '=', 1);
		});
		$this->assertEquals('SELECT * FROM "books" WHERE  (  "id" = ?  ) ', $q->getSqlString());
	}

	public function testAndWhere()  {
		$q = new Query($this->pdo);

		$q->table('books')->where('id', '=', 1)->where('id', '=', 2);
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" = ? AND "id" = ? ', $q->getSqlString());
	}

	public function testOrWhere()  {
		$q = new Query($this->pdo);

		$q->table('books')->orWhere('id', '=', 1)->orWhere('uid', '=', 2);
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" = ? OR "uid" = ? ', $q->getSqlString());
	}

	public function testWhereIn()  {
		$q = new Query($this->pdo);

		$q->table('books')->whereIn('id', [1]);
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" IN(?) ', $q->getSqlString());
	}

	public function testOrWhereIn()  {
		$q = new Query($this->pdo);

		$q->table('books')->whereIn('id', [1])->orWhereIn('id', [2]);
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" IN(?) OR "id" IN(?) ', $q->getSqlString());
	}

	public function testWhereNotIn()  {
		$q = new Query($this->pdo);

		$q->table('books')->whereNotIn('id', [1]);
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" NOT IN(?) ', $q->getSqlString());
	}

	public function testOrWhereNotIn()  {
		$q = new Query($this->pdo);

		$q->table('books')->orWhereNotIn('id', [1])->orWhereNotIn('uid', [2]);
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" NOT IN(?) OR "uid" NOT IN(?) ', $q->getSqlString());
	}

	public function testWhereIsNull()  {
		$q = new Query($this->pdo);

		$q->table('books')->whereIsNull('id');
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" IS NULL ', $q->getSqlString());
	}

	public function testOrWhereIsNull()  {
		$q = new Query($this->pdo);

		$q->table('books')->orWhereIsNull('id')->orWhereIsNull('uid');
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" IS NULL OR "uid" IS NULL ', $q->getSqlString());
	}

	public function testWhereIsNotNull()  {
		$q = new Query($this->pdo);

		$q->table('books')->whereIsNotNull('id');
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" IS NOT NULL ', $q->getSqlString());
	}

	public function testOrWhereIsNotNull()  {
		$q = new Query($this->pdo);

		$q->table('books')->orWhereIsNotNull('id')->orWhereIsNotNull('uid');
		$this->assertEquals('SELECT * FROM "books" WHERE  "id" IS NOT NULL OR "uid" IS NOT NULL ', $q->getSqlString());
	}

}
