<?php

namespace DB;

interface GrammarInterface {

	public function columns(array $columns): string;

	public function column(string $str): string;

	public function wrap(string $str): string;

	public function placeholders(array $items): string;

}
