## Examples

	// setup database (sqlite or mysql)
	$pdo = new PDO('sqlite::memory:');
	$pdo->exec('CREATE TABLE IF NOT EXISTS "users" ("id" INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT, "name" TEXT NOT NULL)');

	$query = new DB\Query($pdo);
	$query->table('users')->insert(['name' => 'Bob']);

	$prototype = new DB\Row;
	$users = new DB\Table($query, $prototype, 'users', 'id');

	// get first user and update name
	$user = $users->fetch();
	$user->name = 'John';
	$users->save($user);

	// add a new user
	$user = new DB\Row(['name' => 'Dave']);
	$users->save($user);

	// get all users
	$results = $users->get();

	// with the name dave
	$results = $users->where('name', 'like', 'dave%')->get();

	// get executed queries
	$query->getProfile();
