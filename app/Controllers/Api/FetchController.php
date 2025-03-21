<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Library\FeedReader;
use App\Models\Articles;
use App\Models\Feeds;
use wlib\Application\Controllers\AllowLoggedInUsersTrait;
use wlib\Application\Controllers\RestController;

class FetchController extends RestController
{
	use AllowLoggedInUsersTrait;

	/**
	 * @var Feeds
	 */
	private Feeds $feeds;

	/**
	 * @var Articles
	 */
	private Articles $articles;

	public function initialize()
	{
		parent::initialize();

		$this->feeds = $this->app->getTable(Feeds::class);
		$this->articles = $this->app->getTable(Articles::class);
	}

	public function get()
	{
		$id = $this->getFeedIdOrFail();
		$sUrl = $this->feeds->findVal('url', $id);

		$reader = new FeedReader($sUrl);
		$aItems = $reader->fetch();

		if ($aItems === false)
		{
			$this->response->json(['error' => [
				'code' => 404,
				'source' => ['feed' => $sUrl],
				'title' => __('Unreachable feed.')
			]]);
			return;
		}

		$this->articles->resetIsNew($id);
		$this->articles->setPurgeableFeed($id);

		foreach ($aItems as $item)
		{
			$iArticleId = 0;
			$sContentMd5 = md5((string) $item->content);

			$rows = $this->articles->findRows('id, content_md5, emptied_at', [
				'link = '. $this->db->quote($item->link),
				'feed_id = '.(int) $id
			]);

			if ($rows && $row = $rows->fetch())
			{
				$iArticleId = (int) $row->id;

				// No need to update if content unchanged or article emptied
				if ($row->content_md5 == $sContentMd5 || empty($row->emptied_at))
				{
					$this->articles->save(['is_purgeable' => 0], $iArticleId);
					continue;
				}
			}

			$aArticle = [
				'feed_id'		=> $id,
				'title'			=> (string) $item->title,
				'link'			=> (string) $item->link,
				'author'		=> (string) $item->author,
				'category'		=> (string) $item->category,
				'summary'		=> (string) $item->summary,
				'content'		=> (string) $item->content,
				'content_md5'	=> $sContentMd5,
				'pub_date'		=> date('Y-m-d H:i:s', $item->pub_date),
				'is_purgeable'	=> 0
			];

			$this->articles->save($aArticle, $iArticleId);
		}

		$this->articles->purgeFeed($id);
		$this->feeds->save(['last_update' => 'NOW()'], $id);

		$this->response->json(['data' => 'Ok']);
	}

	private function getFeedIdOrFail(): int
	{
		$id = (int) $this->arg(0);

		$id or $this->haltBadRequest(sprintf(
			'Feed ID not provided in URI : "%s/{int:id}/".',
			$this->request->getRequestUri()
		));

		$this->feeds->exists(Feeds::COL_ID_NAME, $id) or $this->haltNotFound(
			'Feed provided not found.'
		);

		return $id;
	}
}