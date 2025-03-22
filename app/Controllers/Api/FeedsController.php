<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Library\FeedReader;
use App\Models\Articles;
use App\Models\Feeds;
use wlib\Application\Controllers\AllowLoggedInUsersTrait;
use wlib\Application\Controllers\RestController;

class FeedsController extends RestController
{
	use AllowLoggedInUsersTrait;

	protected Feeds $feeds;
	protected Articles $articles;

	public function initialize()
	{
		parent::initialize();
		
		$this->feeds = $this->app->getTable(Feeds::class);
	}

	public function get($id)
	{
		($id ? $this->getOne($id) : $this->getList());
	}

	public function post()
	{
		$this->getOne($this->feeds->add($this->request->post()));
	}

	public function put()
	{
		$this->getOne(
			$this->feeds->update(
				$this->getIdOrFail(),
				$this->request->post()
			)
		);
	}

	public function delete()
	{
		$this->feeds->delete($this->getIdOrFail());
	}

	private function getOne($id)
	{
		($feed = $this->feeds->findRow('*', (int) $id))
			or $this->haltNotFound('Feed not found.');

		if ($this->hasParam('fetch'))
		{
			$this->fetch($feed);
		}

		$this->response->json(['data' => $this->buildItem($feed)]);
	}

	private function fetch($feed)
	{
		$reader = new FeedReader($feed->url);
		$feed->articles = $reader->fetch();
	}

	private function getList()
	{
		$data = [];

		$feeds = $this->db->query()
			->select('f.*, COUNT(a.id) AS count_articles')
			->from(Feeds::TABLE_NAME, 'f')
			->leftJoin(
				Articles::TABLE_NAME.' AS a',
				'a.feed_id = f.id AND a.deleted_at IS NULL'
			)
			->orderBy('title')
			->groupBy('f.id')
			->run();

		while ($feed = $feeds->fetch())
			$data['f'.$feed->id] = $this->buildItem($feed);

		$this->response->json(['data' => $data]);
	}

	private function buildItem($feed)
	{
		$id = $feed->id;
		unset($feed->id);

		if (!isset($this->articles))
			$this->articles = $this->app->getTable(Articles::class);

		$feed->count_is_new = $this->articles->getNewArticlesCount($id);
		$feed->count_is_unread = $this->articles->getUnreadArticlesCount($id);

		$result = [
			'type'	=> Feeds::TABLE_NAME,
			'id'	=> $id,
			'attributes' => $feed,
			'links' => ['self' => $this->pathUri() . $id]
		];
		
		return $result;
	}

	private function getIdOrFail()
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