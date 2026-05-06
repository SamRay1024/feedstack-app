<?php declare(strict_types=1);

namespace App\Models;

use RuntimeException;
use UnexpectedValueException;
use wlib\Db\Db;
use wlib\Db\Table;

class UsersParams extends Table
{
	const TABLE_NAME = 'users_params';
	const COL_ID_NAME = 'id';
	const COL_CREATED_AT_NAME = 'created_at';
	const COL_UPDATED_AT_NAME = 'updated_at';

	/**
	 * Run the create table SQL statement.
	 *
	 * @return void
	 */
	public function createTable()
	{
		$sAutoIncrement = ($this->oDb->getDriver() == Db::DRV_SQLTE
			? 'AUTOINCREMENT'
			: 'AUTO_INCREMENT'
		);

		$this->oDb->execute(
			"CREATE TABLE IF NOT EXISTS users_params (
				id INTEGER PRIMARY KEY $sAutoIncrement,
				user_id INTEGER NOT NULL,
				param_name VARCHAR(255) NOT NULL,
				param_value TEXT,
				created_at DATETIME,
				updated_at DATETIME,
				FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
				UNIQUE(user_id, param_name)
			)"
		);
	}

	public function filterFields(array $aFields, $id = 0): array
	{
		$aFiltered = filter_var_array($aFields, [
			'user_id' => FILTER_VALIDATE_INT,
			'param_name' => $this->getFilter('sanitize_string'),
			'param_value' => FILTER_DEFAULT
		], false);
		
		if (!count($aFiltered))
			throw new UnexpectedValueException(
				static::class .' : no data provided. Nothing to do.'
			);

		if ($aFiltered === false)
			throw new RuntimeException(
				static::class.' : an error occured while filtering fields.'
				.' Please check your filters.'
			);

		if (in_array(false, $aFiltered, true))
			throw new UnexpectedValueException(sprintf(
				static::class .' : unexpected value(s) for field(s) "%s".',
				implode(', ', array_keys($aFiltered, false, true))
			), 400);

		// Vérifier les valeurs pour certains paramètres
		if (isset($aFiltered['param_name']) && $aFiltered['param_name'] === 'update_interval')
		{
			$interval = (int)$aFiltered['param_value'];
			$minInterval = 300; // 5 minutes
			$maxInterval = 86400; // 24h
			
			if ($interval < $minInterval || $interval > $maxInterval)
				throw new UnexpectedValueException(sprintf(
					static::class .' : update_interval must be between %d and %d seconds.',
					$minInterval, $maxInterval
				), 400);
		}

		return $aFiltered;
	}

	/**
	 * Get a specific parameter for a user.
	 *
	 * @param int $userId User ID.
	 * @param string $paramName Parameter name.
	 * @param mixed $defaultValue Default value if parameter not found.
	 * @return mixed Parameter value.
	 */
	public function getParam(int $userId, string $paramName, $defaultValue = null)
	{
		$rows = $this->oDb->query()
			->select('param_value')
			->from(self::TABLE_NAME)
			->where('user_id = :user_id AND param_name = :param_name')
			->setParameter('user_id', $userId, \PDO::PARAM_INT)
			->setParameter('param_name', $paramName)
			->run();

		$row = $rows->fetch();
		
		return $row ? $row->param_value : $defaultValue;
	}

	/**
	 * Set a parameter for a user.
	 *
	 * @param int $userId User ID.
	 * @param string $paramName Parameter name.
	 * @param mixed $paramValue Parameter value.
	 * @return bool Success.
	 */
	public function setParam(int $userId, string $paramName, $paramValue)
	{
		$existing = $this->oDb->query()
			->select('id')
			->from(self::TABLE_NAME)
			->where('user_id = :user_id AND param_name = :param_name')
			->setParameter('user_id', $userId, \PDO::PARAM_INT)
			->setParameter('param_name', $paramName)
			->run()
			->fetch();

		if ($existing)
		{
			return $this->save([
				'param_value' => $paramValue
			], (int)$existing->id);
		}
		else
		{
			return $this->save([
				'user_id' => $userId,
				'param_name' => $paramName,
				'param_value' => $paramValue
			]);
		}
	}

	/**
	 * Get all parameters for a user.
	 *
	 * @param int $userId User ID.
	 * @return array All parameters for the user.
	 */
	public function getAllParams(int $userId)
	{
		$params = [];
		$rows = $this->oDb->query()
			->select('param_name, param_value')
			->from(self::TABLE_NAME)
			->where('user_id = :user_id')
			->setParameter('user_id', $userId, \PDO::PARAM_INT)
			->run();

		while ($row = $rows->fetch())
		{
			$params[$row->param_name] = $row->param_value;
		}

		return $params;
	}

	/**
	 * Get or create a parameter for a user.
	 *
	 * @param int $userId User ID.
	 * @param string $paramName Parameter name.
	 * @param mixed $defaultValue Default value.
	 * @return mixed Parameter value.
	 */
	public function getOrCreateParam(int $userId, string $paramName, $defaultValue = null)
	{
		$value = $this->getParam($userId, $paramName);
		
		if ($value === null)
		{
			$this->setParam($userId, $paramName, $defaultValue);
			return $defaultValue;
		}
		
		return $value;
	}
}