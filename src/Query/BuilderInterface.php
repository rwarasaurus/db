<?php

namespace DB\Query;

interface BuilderInterface extends FragmentInterface, BindingsInterface {

	public function getAlias();

}
