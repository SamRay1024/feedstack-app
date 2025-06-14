<?php

$sBasePath = dirname(__DIR__);

return [

	/* ==== GLOBALS ========================================================= */

	'name'				=> env('APP_NAME'),
	'production'		=> env('APP_PRODUCTION'),
	'public_path'		=> $sBasePath .'/public',
	'storage_path'		=> $sBasePath .'/storage',
	'cache_path'		=> $sBasePath .'/storage/cache',
	'logs_path'			=> $sBasePath .'/storage/logs',
	'templates_path'	=> $sBasePath .'/templates',
	'i18n_path'			=> $sBasePath .'/locales',
	'base_url'			=> env('APP_BASE_URL', 'localhost:8000'),
	'base_uri'			=> env('APP_BASE_URI', '/'),
	'psr4_folders'		=> ['App\\' => $sBasePath .'/app'],
	'ns_controllers'	=> 'App\\Controllers',
	'timezone'			=> env('APP_TIMEZONE', 'Europe/Paris'),
	'i18n_locale'		=> env('APP_I18N_LOCALE', 'fr_FR'),
	'private_key'		=> env('APP_PRIVATE_KEY', ''),
	
	/* ==== DATABASES ======================================================= */
	
	'databases'			=> [
		'default' => [
			'driver'	=> env('APP_DB_DRIVER', ''),
			'database'	=> env('APP_DB_DATABASE', ''),
			'host'		=> env('APP_DB_HOST', ''),
			'port'		=> env('APP_DB_PORT', 0),
			'username'	=> env('APP_DB_USERNAME', ''),
			'password'	=> env('APP_DB_PASSWORD', ''),
		],
		// 'other' => [
		// 	'driver'	=> 'mysql',
		// 	'database'	=> 'database',
		// 	'host'		=> 'host',
		// 	'port'		=> 3306,
		// 	'username'	=> 'user',
		// 	'password'	=> 'pass',
		// 	'timeout'	=> null
		// ]
	],
		
	/* ==== SECURITY ======================================================== */
	
	'security' => [

		// Define the user provider type
		// 'user_provider' => 'array',
		'user_provider' => 'database',

		// Config for "array" provider
		'array' => [
			'users' => [
				'admin' => ['password' => 'pass', 'can_login' => true]
			],
			'hash_algo' => 'plaintext'
		],

		// Config for "database" provider
		'database' => [
			'name' => 'default',
			'hash_algo' => 'bcrypt',
			// 'hash_options' => ['cost' => 10]
		],

		// Guard parameters
		'guard' => [
			'web' => [
				'can_register' => env('APP_CAN_REGISTER', false)
			]
		]
	],

	/* ==== MAILER ========================================================== */

	// Mailpit cmd : ./mailpit --smtp  127.0.0.1:1027 --smtp-auth-allow-insecure --smtp-auth-accept-any

	'mailer' => [

		'driver' 	=> 'smtp', // 'mail', 'sendmail' or 'smtp'
		// 'charset'	=> 'utf-8',

		// smtp params below
		'host'			=> 'localhost',
		'port'			=> 1027,
		// 'username'		=> '',
		// 'password'		=> '',
		// 'encryption'	=> PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS

		'from'			=> 'noreply@myapp.com',
		'replyto'		=> 'support@myapp.com'
	]
];