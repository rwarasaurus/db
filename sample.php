<?php

require __DIR__ . '/vendor/autoload.php';

$query = new DB\Query\Builder();

$query->select(['books.id'])
	->table('books')
	->join('authors', 'authors.id', '=', 'books.author')
	->where('authors.id', '=', 4)
	->joinColumns('meta', ['meta.object' => 'books.id', 'meta.key' => 4.5])
	->where('meta.value', '>', '2')
	->group('books.author')
	->whereInQuery('books.category', function($query) {
		$query->select(['categories.id'])->table('categories')
			->joinColumns('meta', ['meta.object' => 'books.id', 'meta.key' => 'notes'])
			->where('categories.slug', '=', 'gaming');
	})
	->orWhereNested(function($where) {
		$where('meta.value', '<', '200')->and('meta.value', '>', '300');
	})
	->leftJoin('reviews', 'reviews.book', '=', 'books.id')
	->whereIn('books.status', ['published', 'archived'])
	;


var_dump($query->getSqlString(), $query->getBindings());
