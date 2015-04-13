<?php

use DB\Query;

class JoinsTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$this->pdo = new PDO('sqlite::memory:');
		$this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

		$this->pdo->exec('PRAGMA synchronous = OFF');
		$this->pdo->exec('PRAGMA journal_mode = OFF');

		$this->pdo->exec('CREATE TABLE [books] ([id] integer PRIMARY KEY AUTOINCREMENT, [title] text, [author] integer)');
		$this->pdo->exec('CREATE TABLE [authors] ([id] integer PRIMARY KEY AUTOINCREMENT, [name] text)');
	}

	protected function tearDown() {
		$this->pdo = null;
	}

	public function testInnerJoin()  {
		$q = new Query($this->pdo);

		$q->table('books')->join('authors', 'authors.id', '=', 'books.author');
		$this->assertEquals('SELECT * FROM "books" INNER JOIN "authors" ON("authors"."id" = "books"."author") ', $q->getSqlString());
	}

	public function testLeftJoin()  {
		$q = new Query($this->pdo);

		$q->table('books')->leftJoin('authors', 'authors.id', '=', 'books.author');
		$this->assertEquals('SELECT * FROM "books" LEFT JOIN "authors" ON("authors"."id" = "books"."author") ', $q->getSqlString());
	}

}
