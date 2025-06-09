<?php declare(strict_types=1);

namespace App\Models;

use PDO;
use RuntimeException;
use wlib\Db\Db;
use wlib\Db\Table;

class Articles extends Table
{
	const TABLE_NAME = 'articles';
	const COL_ID_NAME = 'id';
	const COL_CREATED_AT_NAME = 'created_at';
	const COL_UPDATED_AT_NAME = 'updated_at';
	const COL_DELETED_AT_NAME = 'deleted_at';
	const COL_EMPTIED_AT_NAME = 'emptied_at';

	protected $aColumns = [
		'feed_id'		=> \PDO::PARAM_INT,
		'is_new'		=> \PDO::PARAM_BOOL,
		'is_read'		=> \PDO::PARAM_BOOL,
		'is_archive'	=> \PDO::PARAM_BOOL,
		'is_read_later'	=> \PDO::PARAM_BOOL,
		'is_purgeable'	=> \PDO::PARAM_BOOL
	];
	
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
			"CREATE TABLE IF NOT EXISTS articles (
				id INTEGER PRIMARY KEY $sAutoIncrement,
				feed_id INTEGER NOT NULL,
				title VARCHAR(255) NOT NULL,
				link VARCHAR(255) NOT NULL,
				author VARCHAR(255) NOT NULL,
				category VARCHAR(80) NOT NULL,
				summary TEXT,
				content TEXT,
				content_md5 VARCHAR(32),
				pub_date DATETIME,
				is_new INTEGER NOT NULL DEFAULT 1,
				is_read INTEGER NOT NULL DEFAULT 0,
				is_archive INTEGER NOT NULL DEFAULT 0,
				is_read_later INTEGER NOT NULL DEFAULT 0,
				is_purgeable INTEGER NOT NULL DEFAULT 0,
				created_at DATETIME,
				updated_at DATETIME,
				deleted_at DATETIME,
				emptied_at DATETIME,
				FOREIGN KEY (feed_id) REFERENCES feeds (id) ON DELETE CASCADE
			);"
		);

		if ($this->oDb->getDriver() == Db::DRV_MYSQL)
		{
			$oStatement = $this->oDb->execute(
				'SHOW INDEX FROM articles WHERE Key_name = "idx_pub_date"'
			);
			
			if (!$oStatement->fetch())
				$this->oDb->execute(
					'ALTER TABLE articles ADD INDEX `idx_pub_date` (`pub_date`)'
				);
		}
		else
			$this->oDb->execute(
				'CREATE INDEX IF NOT EXISTS idx_pub_date ON articles (pub_date)'
			);
	}

	public function filterFields(array $aFields, $id = 0): array
	{
		$aFiltered = filter_var_array($aFields, [
			'feed_id'		=> FILTER_VALIDATE_INT,
			'title'			=> $this->getFilter('sanitize_string'),
			'link'			=> FILTER_VALIDATE_URL,
			'author'		=> $this->getFilter('sanitize_string'),
			'category'		=> $this->getFilter('sanitize_string'),
			'summary'		=> FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'content'		=> FILTER_SANITIZE_FULL_SPECIAL_CHARS,
			'content_md5'	=> $this->getFilter('validate_string_alnum'),
			'pub_date'		=> $this->getFilter('sanitize_string'),
			'is_new'		=> FILTER_VALIDATE_BOOL,
			'is_read'		=> FILTER_VALIDATE_BOOL,
			'is_archive'	=> FILTER_VALIDATE_BOOL,
			'is_read_later'	=> FILTER_VALIDATE_BOOL,
			'is_purgeable'	=> FILTER_VALIDATE_BOOL
		], false);
		
		if ($aFiltered === false)
			throw new RuntimeException(
				static::class.' : an error occured while filtering fields.'
				.' Please check your filters.'
			);

		// if (in_array(false, $aFiltered, true))
		// 	throw new UnexpectedValueException(sprintf(
		// 		static::class .' : unexpected value(s) for field(s) "%s".',
		// 		implode(', ', array_keys($aFiltered, false, true))
		// 	), 400);

		// if (isset($aFiltered['url']) && $this->exists('url', $aFiltered['url'], $id))
		// 	throw new UnexpectedValueException(sprintf(
		// 		static::class .' : Feed "%s" already added.',
		// 		$aFiltered['url']
		// 	), 409);

		if (isset($aFiltered['is_read']) && $aFiltered['is_read'])
			$aFiltered['is_new'] = 0;

		if (isset($aFiltered['is_archive']) && $aFiltered['is_archive'])
		{
			$aFiltered['is_new'] = 0;
			$aFiltered['is_read_later'] = 0;
		}
		
		if (isset($aFiltered['is_read_later']) && $aFiltered['is_read_later'])
		{
			$aFiltered['is_read'] = 0;
		}

		return $aFiltered;
	}

	/**
	 * Reset "is_new" status for all articles of the given feed.
	 *
	 * @param integer $iFeedId Feed ID.
	 * @return void
	 */
	public function resetIsNew(int $iFeedId): void
	{
		$this->oDb->query()
			->update(self::TABLE_NAME)
			->set('is_new', 0, \PDO::PARAM_INT)
			->where('feed_id = :id AND '. self::COL_DELETED_AT_NAME .' IS NULL')
			->setParameter('id', $iFeedId)
			->run();
	}

	/**
	 * Count unread articles for the given feed.
	 *
	 * @param int $iFeedId
	 * @return int
	 */
	public function getUnreadArticlesCount(int $iFeedId): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME .' IS NULL AND '
				.'feed_id = :id AND is_read = 0 AND is_read_later = 0 AND is_archive = 0'
			)
			->setParameter('id', $iFeedId)
			->run()->fetchColumn();
	}
	
	/**
	 * Count new articles for the given feed.
	 *
	 * @param int $iFeedId Feed ID.
	 * @return int
	 */
	public function getNewArticlesCount(int $iFeedId): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME .' IS NULL AND '
				.'feed_id = :id AND is_new = 1 AND is_read_later = 0 AND is_archive = 0'
			)
			->setParameter('id', $iFeedId)
			->run()
			->fetchColumn();
	}

	/**
	 * Count today articles.
	 * 
	 * @param bool $bUnreadOnly Count unread articles only.
	 * @return int
	 */
	public function getTodayCount(bool $bUnreadOnly = false): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME.' IS NULL AND '
				.'is_archive = 0 AND created_at >= :created_at AND '
				.'is_read_later = 0'
				.($bUnreadOnly ? ' AND is_read = 0' : '')
			)
			->setParameter('created_at', date('Y-m-d') .' 00:00:00')
			->run()->fetchColumn();
	}
	
	/**
	 * Count read later articles.
	 * 
	 * @param bool $bUnreadOnly Count unread articles only.
	 * @return int
	 */
	public function getReadLaterCount(bool $bUnreadOnly = false): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME.' IS NULL AND '
				.'is_archive = 0 AND is_read_later = 1'
				.($bUnreadOnly ? ' AND is_read = 0' : '')
			)
			->run()->fetchColumn();
	}

	/**
	 * Count archives.
	 * 
	 * @return int
	 */
	public function getArchivesCount(): int
	{
		return $this->makeQueryCount()
			->where(
				self::COL_DELETED_AT_NAME.' IS NULL'
				.' AND is_archive = 1'
			)
			->run()->fetchColumn();
	}
	
	/**
	 * Empty deleted articles.
	 *
	 * @param integer $iArticleId Article ID to empty (0 for all).
	 * @return void
	 */	
	public function empty(int $iArticleId = 0)
	{
		$empty = $this->oDb->query()
			->update(self::TABLE_NAME)
			->set(self::COL_EMPTIED_AT_NAME, 'NOW()');

		$sWhere = self::COL_DELETED_AT_NAME .' IS NOT NULL';
		
		if ($iArticleId != 0)
		{
			$sWhere .= ' AND '. self::COL_ID_NAME .' = :id';
			$empty->setParameter('id', $iArticleId, PDO::PARAM_INT);
		}
	
		$empty->where($sWhere)->run();
	}

	/**
	 * Mark emptied articles of a given feed as purgeable.
	 * 
	 * This status is provided to prevent purge to delete articles that still exists
	 * in feeds and avoid to download again already deleted articles.
	 *
	 * @param integer $iFeedId Feed ID.
	 * @return void
	 */
	public function setPurgeableFeed(int $iFeedId)
	{
		$this->oDb->query()
			->update(self::TABLE_NAME)
			->set('is_purgeable', 1, \PDO::PARAM_INT)
			->where('feed_id = :id AND ' . self::COL_EMPTIED_AT_NAME .' IS NOT NULL')
			->setParameter('id', $iFeedId)
			->run();
	}
	
	/**
	 * Set article as unpurgeable.
	 *
	 * @param mixed $iArticleId Article ID.
	 * @return int|false
	 */
	public function setUnpurgeable(int $iArticleId)
	{
		return $this->save(['is_purgeable' => 0], $iArticleId);
	}

	/**
	 * Delete articles previously emptied.
	 * 
	 * @param int $iArticleId Article ID or 0 for all.
	 * @return void
	 */
	public function purge(int $iArticleId = 0)
	{
		$purge = $this->oDb->query()->delete(self::TABLE_NAME);
		$sWhere = self::COL_EMPTIED_AT_NAME .' IS NOT NULL'
			.' AND is_purgeable = 1';

		if ($iArticleId != 0)
		{
			$sWhere .= ' AND '. self::COL_ID_NAME .' = :id';
			$purge->setParameter('id', $iArticleId, PDO::PARAM_INT);
		}

		$purge->where($sWhere)->run();
	}
		
	/**
	 * Purge articles of the given feed.
	 *
	 * @param int $iFeedId Feed ID.
	 * @return int
	 */
	public function purgeFeed(int $iFeedId): int
	{
		return $this->oDb->query()->delete(self::TABLE_NAME)
			->where(
				'feed_id = :id AND '. self::COL_EMPTIED_AT_NAME .' IS NOT NULL'
				.' AND is_purgeable = 1'
			)
			->setParameter('id', $iFeedId, PDO::PARAM_INT)
			->run();
	}

	/**
	 * Make a query to count on current table.
	 * 
	 * @return \wlib\Db\Query
	 */
	private function makeQueryCount()
	{
		return $this->oDb->query()
			->select('COUNT('. self::COL_ID_NAME .')')
			->from(self::TABLE_NAME);
	}
}