<?php

require __DIR__ . '/vendor/autoload.php';

$pdo = new PDO('sqlite::memory:');
$pdo->exec('CREATE TABLE books (id INT, author INT, category INT, title TEXT, published_date TEXT)');
$pdo->exec('CREATE TABLE authors (id INT, name TEXT)');
$pdo->exec('CREATE TABLE categories (id INT, name TEXT)');
$pdo->exec('CREATE TABLE reviews (id INT, book INT, rating INT, notes TEXT)');

$query = new DB\Query($pdo);

$faker = Faker\Factory::create();

foreach(range(1, 20) as $id) {
	$query->table('reviews')->insert([
		'id' => $id,
		'book' => rand(1, 50),
		'rating' => rand(1, 5),
		'notes' => $faker->text,
	]);
}

foreach(range(1, 20) as $id) {
	$query->table('authors')->insert([
		'id' => $id,
		'name' => $faker->name,
	]);
}

foreach(range(1, 20) as $id) {
	$query->table('categories')->insert([
		'id' => $id,
		'name' => $faker->word,
	]);
}

$now = date('Y-m-d H:i:s');

foreach(range(1, 50) as $id) {
	$query->table('books')->insert([
		'id' => $id,
		'author' => rand(1, 20),
		'category' => rand(1, 20),
		'title' => $faker->sentence(rand(2, 8)),
		'published_date' => $now,
	]);
}

$results = $query->table('books')
	->select(['books.title', 'categories.name'])
	->where('books.published_date', '>=', $now)
	->join('categories', 'categories.id', '=', 'books.category')
	->whereNested(function($where) {
		$where('categories.id', '=', 7)->or('categories.id', '=', 2);
	})
	->joinWhere('reviews', function($join) {
		$join('reviews.book', '=', 'books.id')->and('reviews.rating', '=', 5);
	})
	->get();


echo $query->getLastSqlString().PHP_EOL;
