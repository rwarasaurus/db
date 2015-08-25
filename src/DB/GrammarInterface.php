<?php

namespace DB;

class GrammarInterface {

	public function columns(array $columns);

	public function column($str);

	public function alias($str);

	public function wrap($str);

	public function placeholders(array $items);

}
