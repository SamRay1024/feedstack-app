<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\UsersParams;
use wlib\Application\Controllers\AllowLoggedInUsersTrait;
use wlib\Application\Controllers\RestController;

class ParamsController extends RestController
{
	use AllowLoggedInUsersTrait;

	/**
	 * @var UsersParams
	 */
	private UsersParams $params;

	public function initialize()
	{
		parent::initialize();

		$this->params = $this->app->getTable(UsersParams::class);
	}

	/**
	 * Get all user parameters.
	 */
	public function get()
	{
		$userId = (int) $this->user->getId();
		$params = $this->params->getAllParams($userId);
		
		// Set default values for missing parameters
		if (!isset($params['update_interval']))
			$params['update_interval'] = $this->getDefaultValue('update_interval');

		$this->response->json(['data' => $params]);
	}

	/**
	 * Get a specific parameter.
	 */
	public function getParam()
	{
		$paramName = $this->arg(0);
		
		if (empty($paramName))
			$this->haltBadRequest('Parameter name is required');

		$userId = (int) $this->user->getId();
		$value = $this->params->getOrCreateParam($userId, $paramName, $this->getDefaultValue($paramName));

		$this->response->json(['data' => $value]);
	}

	/**
	 * Update a parameter.
	 */
	public function put()
	{
		$paramName = $this->arg(0);
		
		if (empty($paramName))
			$this->haltBadRequest('Parameter name is required');

		// Récupérer les données du body JSON
		$body = json_decode($this->rawData(), true);
		$paramValue = $body['value'] ?? null;
		
		if ($paramValue === null)
			$this->haltBadRequest('Parameter value is required');

		$userId = (int) $this->user->getId();
		$this->params->setParam($userId, $paramName, $paramValue);

		$this->response->json(['data' => $paramValue]);
	}

	/**
	 * Update multiple parameters.
	 */
	public function patch()
	{
		$params = json_decode($this->rawData(), true);
		
		if (empty($params))
			$this->haltBadRequest('Parameters are required');

		$userId = (int) $this->user->getId();
		$updatedParams = [];

		foreach ($params as $name => $value)
		{
			$this->params->setParam($userId, $name, $value);
			$updatedParams[$name] = $value;
		}

		$this->response->json(['data' => $updatedParams]);
	}

	/**
	 * Get default value for a parameter.
	 */
	private function getDefaultValue($paramName)
	{
		switch ($paramName)
		{
			case 'update_interval':
				return 1800; // 30 minutes
			
			default:
				return null;
		}
	}
}
