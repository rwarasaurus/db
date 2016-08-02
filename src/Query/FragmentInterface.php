<?php

namespace DB\Query;

interface FragmentInterface {

	public function getSqlString(): string;

}
