<?php

spl_autoload_register(function($class) {
	if(is_file($path = __DIR__ . '/../src/' . str_replace('\\', '/', $class) . '.php')) {
		return require $path;
	}
});
