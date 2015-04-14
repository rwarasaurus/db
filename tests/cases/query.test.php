<?php

use DB\Query;

class QueryTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->pdo = new PDO('sqlite::memory:');
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		$this->pdo->exec('CREATE TABLE [books] ([id] integer PRIMARY KEY AUTOINCREMENT, [title] text, [author] integer)');

		$stm = $this->pdo->prepare('INSERT INTO [books] ([id], [title], [author]) VALUES(?, ?, ?)');

		foreach(range(1, 20) as $index) {
			$stm->execute(array(null, 'Book '.$index, $index));
		}

		$this->pdo->exec('CREATE TABLE [authors] ([id] integer PRIMARY KEY AUTOINCREMENT, [name] text)');

		$stm = $this->pdo->prepare('INSERT INTO [authors] ([id], [name]) VALUES(?, ?)');

		foreach(range(1, 20) as $index) {
			$stm->execute(array(null, 'Author '.$index));
		}
	}

	protected function tearDown() {
		$this->pdo = null;
	}

	public function testSelect()  {
		$q = new Query($this->pdo);

		$q->select(['books.id as book_id', 'books.*']);
		$this->assertEquals('SELECT "books"."id" AS "book_id", "books".*', $q->getSqlString());
	}

	public function testTable()  {
		$q = new Query($this->pdo);

		$q->table('books');
		$this->assertEquals('SELECT * FROM "books"', $q->getSqlString());
	}

	public function testGroup()  {
		$q = new Query($this->pdo);

		$q->table('books')->group('id');
		$this->assertEquals('SELECT * FROM "books" GROUP BY "id"', $q->getSqlString());
	}

	public function testSort()  {
		$q = new Query($this->pdo);

		$q->table('books')->sort('id', 'desc');
		$this->assertEquals('SELECT * FROM "books" ORDER BY "id" DESC', $q->getSqlString());
	}

	public function testLimits()  {
		$q = new Query($this->pdo);

		$q->table('books')->take(3)->skip(8);
		$this->assertEquals('SELECT * FROM "books" LIMIT 3 OFFSET 8', $q->getSqlString());
	}

	public function testExec()  {
		$q = new Query($this->pdo);

		$q->exec('SELECT 1');
		$this->assertEquals('SELECT 1', $q->getLastSqlString());
	}

	public function testException()  {
		$q = new Query($this->pdo);

		$this->setExpectedException('ErrorException');
		$q->exec('SELECT FAIL()');
	}

	public function testFetch()  {
		$q = new Query($this->pdo);

		$r = $q->table('books')->fetch();
		$this->assertEquals('1', $r->id);
	}

	public function testGet()  {
		$q = new Query($this->pdo);

		$r = $q->table('books')->get();
		$this->assertEquals('1', $r[0]->id);
	}

	public function testCount()  {
		$q = new Query($this->pdo);

		$r = $q->table('books')->count();
		$this->assertEquals('20', $r);
	}

	public function testSum()  {
		$q = new Query($this->pdo);

		$r = $q->table('books')->sum('id');
		$this->assertEquals('210', $r);
	}

	public function testHydrateModel()  {
		$q = new Query($this->pdo);
		$p = new DB\Row;
		$r = $q->prototype($p)->table('books')->fetch();
		$this->assertEquals('1', $r->id);
	}

	public function testUpdate()  {
		$q = new Query($this->pdo);

		$q->table('books')->where('id', '=', 5)->update(['author' => 1]);
		$this->assertEquals('UPDATE "books" SET "author" = ? WHERE  "id" = ? ', $q->getLastSqlString());
	}

	public function testInsert()  {
		$q = new Query($this->pdo);

		$q->table('books')->insert(['title' => 'test', 'author' => 1]);
		$this->assertEquals('INSERT INTO "books" ("title", "author") VALUES(?, ?)', $q->getLastSqlString());
	}

	public function testDelete()  {
		$q = new Query($this->pdo);

		$q->table('books')->where('id', '=', 5)->delete();
		$this->assertEquals('DELETE FROM "books" WHERE  "id" = ? ', $q->getLastSqlString());
	}

	public function testIncr()  {
		$q = new Query($this->pdo);

		$q->table('books')->where('id', '=', 5)->incr('author');
		$this->assertEquals('UPDATE "books" SET "author" = "author" + 1 WHERE  "id" = ? ', $q->getLastSqlString());
	}

	public function testDecr()  {
		$q = new Query($this->pdo);

		$q->table('books')->where('id', '=', 5)->decr('author');
		$this->assertEquals('UPDATE "books" SET "author" = "author" + -1 WHERE  "id" = ? ', $q->getLastSqlString());
	}

	public function testRowArray()  {
		$q = new Query($this->pdo);
		$p = new DB\Row;
		$r = $q->prototype($p)->table('books')->fetch();
		$this->assertInternalType('array', $r->toArray());
	}

	public function testRowJson()  {
		$q = new Query($this->pdo);
		$p = new DB\Row;
		$r = $q->prototype($p)->table('books')->fetch();
		$this->assertEquals(json_encode($r->toArray()), $r->toJson());
	}

	public function testRowString()  {
		$q = new Query($this->pdo);
		$p = new DB\Row;
		$r = $q->prototype($p)->table('books')->fetch();
		$this->assertEquals(json_encode($r->toArray()), (string) $r);
	}

	public function testProfile()  {
		$q = new Query($this->pdo);

		$q->disableProfile()->table('books')->fetch();

		$this->assertCount(0, $q->getProfile());
	}

	public function testMySQLGrammar()  {
		$q = new Query($this->pdo);

		$q->driver('mysql')->select(['books.id']);
		$this->assertEquals('SELECT `books`.`id`', $q->getSqlString());
	}

	public function testSqliteGrammar()  {
		$q = new Query($this->pdo);

		$q->driver('sqlite')->select(['books.id']);
		$this->assertEquals('SELECT "books"."id"', $q->getSqlString());
	}

	public function testDriverException()  {
		$q = new Query($this->pdo);

		$this->setExpectedException('ErrorException');
		$q->driver('pgsql');
	}

}
