<?php declare(strict_types=1);

namespace App\Providers;

use wlib\Application\Sys\Kernel;
use wlib\Di\DiBox;
use wlib\Di\DiBoxProvider;
use wlib\Tools\Hooks;

class SetupDiProvider implements DiBoxProvider
{
	public function provide(DiBox $box) {}

	public function boot(Kernel $app)
	{
		if (!file_exists($app->get('sys.env_file')))
		{
			Hooks::add('wlib.app.router.dispatch.after', function ($args)
			{
				$args['route']['controller_fqcn'] = 'App\Controllers\SetupController';
			});
		}
	}
}