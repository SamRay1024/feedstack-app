<?php

return [

	/* ==== GLOBALS ========================================================= */

	'name'				=> 'FeedStack',
	'production'		=> false,
	'public_path'		=> ROOT_DIR .'/public',
	'storage_path'		=> ROOT_DIR .'/storage',
	'cache_path'		=> ROOT_DIR .'/storage/cache',
	'logs_path'			=> ROOT_DIR .'/storage/logs',
	'templates_path'	=> ROOT_DIR .'/templates',
	'i18n_path'			=> ROOT_DIR .'/locales',
	'base_url'			=> 'localhost:8000',
	'base_uri'			=> '/',
	'psr4_folders'		=> ['App\\' => ROOT_DIR .'/app'],
	'ns_controllers'	=> 'App\\Controllers',
	'timezone'			=> 'Europe/Paris',
	'i18n_locale'		=> 'fr_FR',
	'private_key'		=> file_get_contents(__DIR__.'/pkey.file'),
	
	/* ==== DATABASES ======================================================= */
	
	'databases'			=> [
		'default' => [
			'driver'	=> 'sqlite',
			'database'	=> ROOT_DIR.'/storage/db.sqlite'
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
				'can_register' => true
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