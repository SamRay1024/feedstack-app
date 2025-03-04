<?php declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\Articles;
use wlib\Application\Controllers\AllowLoggedInUsersTrait;
use wlib\Application\Controllers\RestController;

class ArticlesController extends RestController
{
	use AllowLoggedInUsersTrait;

	protected Articles $articles;

	public function initialize()
	{
		parent::initialize();
		
		$this->articles = $this->app->make(Articles::class, [$this->db]);
	}

	public function get($id)
	{
		(is_numeric($id) ? $this->getOne($id) : $this->search());
	}

	public function delete()
	{
		$this->articles->delete($this->getFeedIdOrFail());
	}

	public function put($id)
	{
		$this->articles->save(
			array_intersect_key($this->data(), array_flip(['is_read', 'is_archive', 'is_read_later'])),
			(int) $id
		);
	}

	private function getOne($id)
	{
		($feed = $this->articles->findRow('*', (int) $id))
			or $this->haltNotFound('Article not found.');

		$this->response->json(['data' => $this->buildItem($feed)]);
	}

	private function search()
	{
		$aData = [];
		$aWhere = [
			'deleted'	=> 'deleted_at IS NULL',
			'later'		=> 'is_read_later = 0',
			'archive'	=> 'is_archive = '.($this->hasParam('archives') ? '1' : '0')
		];

		if ($this->hasParam('feed'))
			$aWhere[] = 'feed_id = '.(int) $this->param('feed');

		if ($this->hasParam('today'))
		{
			$aWhere[] = 'created_at >= "'.date('Y-m-d').' 00:00:00"';
			$aWhere['archive'] = 'is_archive = 0';
		}
		
		if ($this->hasParam('later'))
		{
			$aWhere['later'] = 'is_read_later = 1';
			$aWhere['archive'] = 'is_archive = 0';
		}

		$articles = $this->articles->findRows('*', $aWhere, 'pub_date DESC');

		while ($article = $articles->fetch())
			$aData[] = $this->buildItem($article);

		$this->response->json(['data' => $aData]);
	}

	private function buildItem($article)
	{
		$id = $article->id;
		unset($article->id);

		$article->title = html_entity_decode($article->title);
		$article->pub_date = date('d/m/Y, H\hi', strtotime($article->pub_date));
		$article->content = html_entity_decode($article->content);
		$article->summary = html_entity_decode($article->summary);

		if ($article->content == '')
			$article->content = $article->summary;

		$article->content = preg_replace('`\</*div.*\>`U', '', $article->content);

		return [
			'type'	=> Articles::TABLE_NAME,
			'id'	=> $id,
			'attributes' => $article,
			'links' => ['self' => $this->pathUri() . $id]
		];
	}	

	private function getFeedIdOrFail()
	{
		$id = (int) $this->arg(0);

		$id or $this->haltBadRequest(sprintf(
			'Feed ID not provided in URI : "%s{int:id}/".',
			$this->request->getRequestUri()
		));

		$this->articles->exists(Articles::COL_ID_NAME, $id) or $this->haltNotFound(
			'Feed provided not found.'
		);

		return $id;
	}
}