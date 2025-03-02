<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Articles;
use wlib\Application\Controllers\AllowLoggedInUsersTrait;
use wlib\Application\Controllers\RestController;

class GlobalsController extends RestController
{
	use AllowLoggedInUsersTrait;

	public function get()
	{
		switch ($this->arg(0, ''))
		{
			case 'counts':
				$articles = $this->app->make(Articles::class, [$this->db]);

				$this->response->json(['data' => [
					'today'	=> $articles->getTodayCount(true),
					'later'	=> $articles->getReadLaterCount(true)
				]]);
				break;
			
			default:
				$this->haltNotFound('No route found.');
		}
	}
}