<?php

namespace spec\DB;

use PDO;
use PhpSpec\ObjectBehavior;

class QuerySpec extends ObjectBehavior {

	protected function getPdoInstance() {
		$pdo = new PDO('sqlite::memory:');
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		$pdo->setAttribute(PDO::ATTR_CURSOR, PDO::CURSOR_SCROLL);

		$pdo->exec('CREATE TABLE [books] ([id] integer PRIMARY KEY AUTOINCREMENT, [title] text, [author] integer)');

		$stm = $pdo->prepare('INSERT INTO [books] ([id], [title], [author]) VALUES(?, ?, ?)');

		foreach(range(1, 20) as $index) {
			$stm->execute(array(null, 'Book '.$index, $index));
		}

		$pdo->exec('CREATE TABLE [authors] ([id] integer PRIMARY KEY AUTOINCREMENT, [name] text)');

		$stm = $pdo->prepare('INSERT INTO [authors] ([id], [name]) VALUES(?, ?)');

		foreach(range(1, 20) as $index) {
			$stm->execute(array(null, 'Author '.$index));
		}

		return $pdo;
	}

	public function let() {
		$this->beConstructedWith($this->getPdoInstance());
		$this->shouldBeAnInstanceOf('DB\Query');
	}

	public function it_should_run_select_statements() {
		$this->table('books')->select(['books.id']);
		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT "books"."id" FROM "books"');
	}

	public function it_should_run_table_statements() {
		$this->table('books');
		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT * FROM "books"');
	}

	public function it_should_run_group_statements() {
		$this->table('books')->group('id');
		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT * FROM "books" GROUP BY "id"');
	}

	public function it_should_run_sort_statements() {
		$this->table('books')->sort('id', 'desc');
		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT * FROM "books" ORDER BY "id" DESC');
	}

	public function it_should_run_limit_statements() {
		$this->table('books')->take(3)->skip(8);
		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT * FROM "books" LIMIT 3 OFFSET 8');
	}

	public function it_should_throw_exceptions() {
		$this->shouldThrow('\DB\SqlException')->during('exec', ['SELECT * FROM notable']);
	}

	public function it_should_run_fetch_statements() {
		$this->table('books')->fetch()->id->shouldBeEqualTo('1');
	}

	public function it_should_run_get_statements() {
		$this->table('books')->get()->shouldBeArray();
	}

	public function it_should_run_count_statements() {
		$this->table('books')->count('id')->shouldBeEqualTo('20');
	}

	public function it_should_run_sum_statements() {
		$this->table('books')->sum('id')->shouldBeEqualTo('210');
	}

	public function it_should_run_update_statements()  {
		$this->table('books')->where('id', '=', 5)->orWhere('id', '=', 2)->update(['author' => 1]);
		$this->getLastSqlString()->shouldBeEqualTo('UPDATE "books" SET "author" = ? WHERE "id" = ? OR "id" = ?');
	}

	public function it_should_run_insert_statements()  {
		$this->table('books')->insert(['title' => 'test', 'author' => 1]);
		$this->getLastSqlString()->shouldBeEqualTo('INSERT INTO "books" ("title", "author") VALUES(?, ?)');
	}

	public function it_should_run_delete_statements()  {
		$this->table('books')->where('id', '=', 5)->delete();
		$this->getLastSqlString()->shouldBeEqualTo('DELETE FROM "books" WHERE "id" = ?');
	}

	public function it_should_run_incr_statements()  {
		$this->table('books')->where('id', '=', 5)->incr('author');
		$this->getLastSqlString()->shouldBeEqualTo('UPDATE "books" SET "author" = "author" + 1 WHERE "id" = ?');
	}

	public function it_should_run_decr_statements()  {
		$this->table('books')->where('id', '=', 5)->decr('author');
		$this->getLastSqlString()->shouldBeEqualTo('UPDATE "books" SET "author" = "author" + -1 WHERE "id" = ?');
	}

	public function it_should_run_inner_join_statements()  {
		$this->table('books')->join('authors', 'authors.id', '=', 'books.author')->get();
		$this->getLastSqlString()->shouldBeEqualTo('SELECT * FROM "books" INNER JOIN "authors" ON("authors"."id" = "books"."author")');
	}

	public function it_should_run_left_join_statements() {
		$this->table('books')->leftJoin('authors', 'authors.id', '=', 'books.author')->get();
		$this->getLastSqlString()->shouldBeEqualTo('SELECT * FROM "books" LEFT JOIN "authors" ON("authors"."id" = "books"."author")');
	}

	public function it_should_run_nested_statements() {
		$this->table('books')->whereNested(function($query) {
			$query('id', '=', 1)->or('id', '=', 3);
		})->fetch();
		$this->getLastSqlString()->shouldBeEqualTo('SELECT * FROM "books" WHERE ( "id" = ? OR "id" = ? )');
	}

	public function it_should_run_where_in_statements() {
		$this->table('books')->whereIn('id', [1, 2, 3])->fetch();
		$this->getLastSqlString()->shouldBeEqualTo('SELECT * FROM "books" WHERE "id" IN (?, ?, ?)');
	}

	public function it_should_run_where_in_select_statements() {
		$this->table('books')->whereInQuery('id', function($query) {
			$query->select(['id'])->table('authors')->whereIn('id', [1, 2, 3]);
		});

		$this->getBuilder()->getBindings()->shouldContain(1);
		$this->getBuilder()->getBindings()->shouldContain(2);
		$this->getBuilder()->getBindings()->shouldContain(3);

		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT * FROM "books" WHERE "id" IN (SELECT "id" FROM "authors" WHERE "id" IN (?, ?, ?))');
	}

	public function it_should_run_where_is_null_statements() {
		$this->table('books')->whereIsNull('id')->fetch();
		$this->getLastSqlString()->shouldBeEqualTo('SELECT * FROM "books" WHERE "id" IS NULL');
	}

	public function it_should_run_complex_statements() {
		$this->select(['books.title'])
			->table('books')
			->join('authors', 'authors.id', '=', 'books.author')
			->where('authors.id', '=', 6)
			->whereNested(function($where) {
				$where('books.id', '=', 1)->or('books.id', '=', 3);
			})
			->where('books.title', 'NOT LIKE', '%game of thrones%')
			->fetch();

		$this->getLastSqlString()->shouldBeEqualTo('SELECT "books"."title" FROM "books" INNER JOIN "authors" ON("authors"."id" = "books"."author") WHERE "authors"."id" = ? AND ( "books"."id" = ? OR "books"."id" = ? ) AND "books"."title" NOT LIKE ?');
	}

	public function it_should_run_joins_and_where_in_any_order() {
		$this->table('books')
			->where('authors.id', '=', 6)
			->joinColumns('authors', [
				'authors.id' => 'books.author',
			])
			->where('books.title', 'NOT LIKE', 'bar');

		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT * FROM "books" INNER JOIN "authors" ON("authors"."id" = "books"."author") WHERE "authors"."id" = ? AND "books"."title" NOT LIKE ?');
		$this->getBuilder()->getBindings()->shouldBeEqualTo([6, 'bar']);
	}

	public function it_should_use_full_text_search() {
		$this->table('books')
			->matchAgainst('foo', ['books.author'], 'NATURAL LANGUAGE');

		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT * FROM "books" WHERE MATCH("books"."author") AGAINST(? IN NATURAL LANGUAGE MODE)');
		$this->getBuilder()->getBindings()->shouldBeEqualTo(['foo']);
	}

	public function it_should_run_expresions(\DB\Query\FragmentInterface $expresion) {
		$expresion->getSqlString()->willReturn('COUNT(*)');

		$this->table('books')->select([$expresion]);

		$this->getBuilder()->getSqlString()->shouldBeEqualTo('SELECT COUNT(*) FROM "books"');
		$this->getBuilder()->getBindings()->shouldBeEqualTo([]);
	}

}
