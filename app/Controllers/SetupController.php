<?php

namespace App\Controllers;

use wlib\Application\Controllers\Controller;

class SetupController extends Controller
{
	public function start()
	{
		$sSubRoute = $this->arg(0);

		if (method_exists($this, $sSubRoute))
		{
			$this->$sSubRoute();
			return;
		}

		$this->db->execute(
			'CREATE TABLE IF NOT EXISTS feeds (
				id INTEGER PRIMARY KEY,
				title VARCHAR(255) NOT NULL,
				url VARCHAR(255) NOT NULL UNIQUE,
				last_update DATETIME,
				created_at DATETIME,
				updated_at DATETIME
			)'
		);

		$this->db->execute(
			'CREATE TABLE IF NOT EXISTS articles (
				id INTEGER PRIMARY KEY,
				feed_id INTEGER NOT NULL,
				title VARCHAR(255) NOT NULL,
				link VARCHAR(255) NOT NULL,
				author VARCHAR(255) NOT NULL,
				category VARCHAR(80) NOT NULL,
				summary TEXT,
				content TEXT,
				content_md5 VARCHAR(32),
				pub_date DATETIME,
				is_new INTEGER NOT NULL DEFAULT (1),
				is_read INTEGER NOT NULL DEFAULT (0),
				is_archive INTEGER NOT NULL DEFAULT (0),
				is_read_later INTEGER NOT NULL DEFAULT (0),
				created_at DATETIME,
				updated_at DATETIME,
				deleted_at DATETIME,
				emptied_at DATETIME,
				FOREIGN KEY (feed_id) REFERENCES feeds (id) ON DELETE CASCADE
			);'
		);

		$this->db->execute(
			'CREATE INDEX IF NOT EXISTS idx_pub_date ON articles (pub_date)'
		);

		// $this->db->execute('DROP TABLE IF EXISTS users');
		$this->db->execute(
			'CREATE TABLE IF NOT EXISTS users (
				id INTEGER PRIMARY KEY,
				name VARCHAR NOT NULL,
				email VARCHAR NOT NULL UNIQUE,
				password VARCHAR,
				token VARCHAR,
				can_login INTEGER,
				created_at DATETIME,
				updated_at DATETIME,
				verified_at DATETIME,
				deleted_at DATETIME
			);'
		);

		$this->response->html('<pre>SETUP DATABASE » OK</pre>');
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
