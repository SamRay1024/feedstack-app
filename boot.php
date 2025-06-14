<?php

$cpr = require_once __DIR__ .'/vendor/autoload.php';

$app = new wlib\Application\Sys\Kernel(__DIR__, [
	'sys.config_dir'	=> 'config',
	'sys.composer'		=> &$cpr,
]);

// Register your dependencies before running app
// $app->register(App\Providers\MyDiProvider::class);
$app->register(wlib\Application\Sys\TracyDiProvider::class);
$app->register(\App\Providers\SetupDiProvider::class);

$app->run();