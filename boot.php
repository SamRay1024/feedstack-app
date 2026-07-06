<?php

$cpr = require_once __DIR__ .'/vendor/autoload.php';

$app = new wlib\Application\Sys\Kernel(__DIR__, [
	'sys.config_dir'	=> 'config',
	'sys.composer'		=> &$cpr,
]);

$app->run();