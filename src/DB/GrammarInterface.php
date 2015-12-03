<?php

namespace DB;

interface GrammarInterface {

	public function columns(array $columns);

	public function column($str);

	public function wrap($str);

	public function placeholders(array $items);

}
