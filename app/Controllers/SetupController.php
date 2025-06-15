<?php

namespace App\Controllers;

use App\Models\Articles;
use App\Models\Feeds;
use wlib\Application\Controllers\FrontController;
use wlib\Application\Crypto\HashManager;
use wlib\Application\Models\User;
use wlib\Db\Db;

class SetupController extends FrontController
{
	private $aFormData = [];
	private $aFormErrors = [];
	private $oDb = null;
	private $sEnvContent = '';

	public function start()
	{
		$this->session->start();

		if (!$this->checkPathsWriteable())
			return;

		if (!$this->handleSetupSubmit())
			$this->showSetupForm();
		else
			$this->install();
		
		return;

		$sSubRoute = $this->arg(0);

		if (method_exists($this, $sSubRoute))
		{
			$this->$sSubRoute();
			return;
		}
	}
	
	/**
	 * Helper to render a screen of the setup.
	 *
	 * @param string $sScreen Screen string, var `$screen` in template file.
	 * @param array $aVars Other variables to pass to the template file.
	 */
	private function renderScreenResponse(string $sScreen, array $aVars)
	{
		$this->response->html($this->render('setup',
			[
				'token' => $this->getFormToken(),
				'screen' => $sScreen
			]
			+ $aVars
		));
	}

	/**
	 * Check system paths are writeable.
	 *
	 * @return bool
	 */
	private function checkPathsWriteable(): bool
	{
		$bStorageIsWriteable = is_writeable(config('app.storage_path'));
		$bCacheIsWriteable = is_writeable(config('app.cache_path'));
		$bLogsIsWriteable = is_writeable(config('app.logs_path'));

		if (!$bStorageIsWriteable || !$bCacheIsWriteable || !$bLogsIsWriteable)
		{
			$this->renderScreenResponse('paths_rights', [
				'storage_ok' => $bStorageIsWriteable,
				'cache_ok' => $bCacheIsWriteable,
				'logs_ok' => $bLogsIsWriteable
			]);

			return false;
		}

		return true;
	}

	private function showSetupForm()
	{
		$this->renderScreenResponse('setup_form', [
			'errors' => $this->aFormErrors,
			'data' => $this->aFormData
		]);
	}

	private function showEnvCreationError()
	{
		$this->renderScreenResponse('env_error', [
			'env_content' => $this->sEnvContent
		]);
	}

	private function showSuccess()
	{
		$this->renderScreenResponse('success', []);
	}

	private function handleSetupSubmit()
	{
		if ($this->isPost() && $this->hasData('install'))
		{
			try
			{
				// $this->checkFormToken();

				$aResult = $this->filterData($this->data(), [
					'db_driver' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('Select a database type.')
					],
					'db_host' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('Database host can\'t be empty.'),
						'empty_if' => ['db_driver' => 'mysql']
					],
					'db_port' => [
						'filter' => FILTER_VALIDATE_INT,
						'value_error' => __('Database port must be an integer.'),
						'empty_if' => ['db_driver' => 'mysql']
					],
					'db_username' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('Database username can\'t be empty.'),
						'empty_if' => ['db_driver' => 'mysql']
					],
					'db_pwd' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_if' => ['db_driver' => 'mysql']
					],
					'db_database' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('Database can\'t be empty.'),
						'empty_if' => ['db_driver' => 'mysql']
					],
					'user_name' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('User name can\'t be empty.')
					],
					'user_email' => [
						'filter' => FILTER_VALIDATE_EMAIL,
						'value_error' => __('User email must be a valid email address.')
					],
					'user_pwd' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('User password can\'t be empty.')
					],
					'timezone' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('Timezone can\'t be empty.')
					],
					'i18n_locale' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
					],
					'can_register' => FILTER_VALIDATE_BOOL,
					'mailer_driver' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('Select a mailer driver.'),
						'empty_if' => ['can_register' => true]
					],
					'mailer_smtp_host' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH,
						'empty_error' => __('SMTP host can\'t be empty.'),
						'empty_if' => ['can_register' => true, 'mailer_driver' => 'smtp']
					],
					'mailer_smtp_port' => [
						'filter' => FILTER_VALIDATE_INT,
						'value_error' => __('SMTP port must be an integer.'),
						'empty_if' => ['can_register' => true, 'mailer_driver' => 'smtp']
					],
					'mailer_smtp_username' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
					],
					'mailer_smtp_password' => [
						'filter' => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
						'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH
					],
					'mailer_from' => [
						'filter' => FILTER_VALIDATE_EMAIL,
						'value_error' => __('Mailer "from" field must be a valid email address.'),
						'empty_error' => __('Mailer "from" field can\'t be empty for handling registrations.'),
						'empty_if' => ['can_register' => true]
					],
					'mailer_replyto' => [
						'filter' => FILTER_VALIDATE_EMAIL,
						'value_error' => __('Mailer "replyto" field must be a valid email address.'),
						'empty_error' => __('Mailer "replyto" field can\'t be empty for handling registrations.'),
						'empty_if' => ['can_register' => true]
					],
				]);

				$this->aFormData = $aResult['data'];
				$this->aFormErrors = $aResult['errors'];

				return (count($this->aFormErrors) == 0);
			}
			catch(\Exception $e)
			{
				$this->aFormErrors[] = $e->getMessage();
			}
		}

		return false;
	}

	private function filterData(array $aData, array $aFilters)
	{
		$aFiltered = filter_var_array($aData, $aFilters);
		$aErrors = [];

		foreach ($aFiltered as $sField => $mValue)
		{
			$bIgnoreError = false;

			if (empty($mValue) && isset($aFilters[$sField]['empty_error']))
			{
				if (isset($aFilters[$sField]['empty_if']))
				{
					foreach ($aFilters[$sField]['empty_if'] as $sDepKey => $sDepValue)
						$bIgnoreError = ($aFiltered[$sDepKey] != $sDepValue);
				}

				if (!$bIgnoreError)
					$aErrors[$sField] = $aFilters[$sField]['empty_error'];
			}

			if ($mValue === false && !$bIgnoreError && isset($aFilters[$sField]['value_error']))
			{
				$aErrors[$sField] = $aFilters[$sField]['value_error'];
			}
		}

		return [
			'data' => $aFiltered,
			'errors' => $aErrors
		];
	}

	private function install()
	{
		if (!$this->connectDb())
			$this->showSetupForm();
		
		elseif (!$this->createDatabase())
			$this->showSetupForm();

		elseif (!$this->createEnvFile())
			$this->showEnvCreationError();

		else
			$this->showSuccess();
	}

	private function connectDb()
	{
		try
		{
			if ($this->aFormData['db_driver'] == 'sqlite')
			{
				$this->aFormData['db_database'] = config('app.storage_path').'/db.sqlite';
				$this->aFormData['db_username'] = null;
				$this->aFormData['db_pwd'] = null;
				$this->aFormData['db_host'] = null;
				$this->aFormData['db_port'] = null;
			}
			
			$this->oDb = new Db(
				$this->aFormData['db_driver'],
				$this->aFormData['db_database'],
				$this->aFormData['db_username'],
				$this->aFormData['db_pwd'],
				$this->aFormData['db_host'],
				$this->aFormData['db_port'],
				'utf8mb4'
			);
			
			$this->oDb->connect();
		}
		catch(\Exception $e)
		{
			$this->aFormErrors[] = _s(
				'Database connection failed. Check the information you have entered.<br /><br />'
				.'Details:<br /><code>%s</code>',
				$e->getMessage()
			);

			return false;
		}

		return true;
	}

	private function createDatabase()
	{
		try
		{
			(new Feeds($this->oDb))->createTable();
			(new Articles($this->oDb))->createTable();

			$user = new User($this->oDb);
			$user->createTable();
		
			if (!$user->findId('email', $this->aFormData['user_email']))
				$user->add([
					'name' => $this->aFormData['user_name'],
					'email' => $this->aFormData['user_email'],
					'password' => $this->hashPassword($this->aFormData['user_pwd']),
					'can_login' => true,
					'verified_at' => 'NOW()'
				]);
		}
		catch(\Exception $e)
		{
			$this->aFormErrors[] = _s(
				'Something gets wrong while database creation.<br /><br />'
				.'Details:<br /><code>%s</code>',
				$e->getMessage()
			);

			return false;
		}

		return true;
	}

	private function createEnvFile()
	{
		$aEnvContent = [
			'APP_PRODUCTION' => 'true',
			'APP_BASE_URL' => server('HTTP_HOST'),
			'APP_BASE_URI' => '/',
			'APP_PRIVATE_KEY' => makePrivateKey('aes-256-xts')
		];
		
		unset(
			$this->aFormData['user_name'],
			$this->aFormData['user_email'],
			$this->aFormData['user_pwd']
		);

		foreach ($this->aFormData as $sKey => $mValue)
		{
			if (is_bool($mValue))
				$mValue = ($mValue ? 'true' : 'false');

			$aEnvContent['APP_'.strtoupper($sKey)] = $mValue;
		}

		$this->sEnvContent = implode("\n", array_map(
			function ($sKey, $mValue)
			{
				return ($mValue == '' ? '#' : '').$sKey.'='.$mValue;
			},
			array_keys($aEnvContent),
			array_values($aEnvContent)
		));
	
		$sEnvFilePath = $this->app->get('sys.base_path').DS.'.env';

		return (file_put_contents($sEnvFilePath, $this->sEnvContent) !== false);
	}
	
	/**
	 * Hash password.
	 *
	 * @param string $sPwd Password to hash.
	 * @return string
	 */
	private function hashPassword(string $sPwd): string
	{
		$sUserProvider = config('app.security.user_provider');

		$hmgr = $this->app->get('hash.manager', [
			config("app.security.$sUserProvider.hash_algo", HashManager::ALGO_BCRYPT),
			config("app.security.$sUserProvider.hash_options", [])
		]);

		return $hmgr->hash($sPwd);
	}

	public function pwd()
	{
		/** @var \wlib\Application\Crypto\HashManager */
		$hasher = $this->app->get('hash.manager', ['bcrypt']);

		echo $hasher->hash($this->arg(1));
	}

	public function pkey()
	{
		$this->response->flush(makePrivateKey());
	}
}
