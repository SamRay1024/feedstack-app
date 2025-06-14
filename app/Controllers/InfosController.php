<?php declare(strict_types=1);

namespace App\Controllers;

use wlib\Application\Controllers\Controller;

class InfosController extends Controller
{
	public function start()
	{
		echo '<pre>';

		echo "## Time : " . date("d/m/Y H:i:s");
		echo "\n## Method : " . $this->method();
		echo "\n## Original method : " . $this->request->getOriginalMethod();

		echo "\n## Headers : ";
		print_r($this->request->getHeaders());

		echo '## Base URI : ' . config('app.base_uri');
		echo "\n## URI : " . $this->request->getRequestUri();

		echo "\n## IP : " . $this->request->getIP();

		echo "\n## User : ";
		print_r($this->user);

		echo "## Route args : ";
		print_r($this->args());

		echo "## Params : ";
		print_r($this->param());

		echo '## Data : ';
		print_r($this->data());

		echo '## Raw params : ';
		print_r($this->rawParam() ?: '--');

		echo "\n## Raw data : ";
		print_r($this->rawData() ?: '--');

		echo "\n## ID string : ";
		print_r($this->getUid());

		echo "\n## Session : ";
		print_r(session_id() ? session() : 'No session');

		echo '</pre>';
	}
}
