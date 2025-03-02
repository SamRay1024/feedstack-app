import * as utils from '../utils.js';

export let articles = {
	iFeedId: 0,
	iFeedIndex: -1,
	iSelectedIndex: -1,
	articles: [],
	iMarkAsReadTimeoutId: 0,

	init()
	{
		window.addEventListener('articles.load.feed', (event) =>
		{
			this.loadFromFeed(event.detail.id, event.detail.index);
		});

		window.addEventListener('articles.load.view', (event) =>
		{
			this.loadFromView(event.detail.view);
		});

		window.addEventListener('articles.delete', (event) =>
		{
			this.delete(event.detail >= 0 ? event.detail : this.iSelectedIndex);
		});

		window.addEventListener('articles.archive', (event) =>
		{
			this.archive(event.detail >= 0 ? event.detail : this.iSelectedIndex);
		});
		
		window.addEventListener('articles.select', (event) =>
		{
			this.select(event.detail);
		});

		window.addEventListener('articles.readLater', (event) =>
		{
			this.readLater(event.detail >= 0 ? event.detail : this.iSelectedIndex);
		});
	},

	loadFromFeed(iFeedId, iFeedIndex)
	{
		this.iFeedId = iFeedId;
		this.iFeedIndex = iFeedIndex;

		this.$store.current.view = '';
		this.$store.current.feed = iFeedId;
		
		utils._fetch('GET', 'articles/?feed='+ iFeedId).then(response =>
		{
			if (response.data)
				this.articles = response.data;
		});
	},

	loadFromView(sView)
	{
		this.iFeedId = sView;

		this.$store.current.view = sView;
		this.$store.current.feed = 0;
		
		utils._fetch('GET', 'articles/?'+ sView).then(response =>
		{
			if (response.data)
			{
				this.articles = response.data;
			}
		});
	},

	select(iIndex)
	{
		if (iIndex < -1) iIndex = -1;
		if (iIndex >= this.articles.length) iIndex = this.articles.length -1;

		this.iSelectedIndex = iIndex;
		this.$dispatch('article.show', {
			'article': (iIndex in this.articles ? this.articles[iIndex] : false),
			'index': iIndex
		});

		if (this.iMarkAsReadTimeoutId)
			clearTimeout(this.iMarkAsReadTimeoutId);

		if (iIndex >= 0 && !this.articles[iIndex].attributes.is_read)
			this.iMarkAsReadTimeoutId = setTimeout(
				(that, iIndex) => { that.markAsRead(iIndex); },
				3000, this, iIndex
			);
	},

	markAsRead(iIndex, bIsRead = true)
	{
		this.articles[iIndex].attributes.is_read = bIsRead;
		this.articles[iIndex].attributes.is_new = false;

		this.$dispatch('feeds.updateCount', {
			'feed_id': this.articles[iIndex].attributes.feed_id,
			'counter_name': 'count_is_unread',
			'diff': (bIsRead ? -1 : 1)
		});

		if (this.$store.current.view != '')
			this.$store.counts[this.$store.current.view]--;
		else
			this.$dispatch('views.reloadCounts');

		utils._fetch('PUT', 'articles/'+ this.articles[iIndex].id, JSON.stringify({is_read: bIsRead}));
	},

	readLater(iIndex, bIsLater = true)
	{
		utils._fetch('PUT', 'articles/'+ this.articles[iIndex].id, JSON.stringify({is_read_later: bIsLater}))
		.then((response) =>
		{
			if (response.status == 204)
			{
				this.articles.splice(iIndex, 1);

				if (this.iSelectedIndex > -1)
					this.select(iIndex);

				this.$store.counts.later++;
			}
		});
	},

	archive(iIndex, bArchive = true)
	{
		if (!(iIndex in this.articles))
			return;

		utils._fetch('PUT', 'articles/'+ this.articles[iIndex].id, JSON.stringify({is_archive: bArchive}))
		.then((response) =>
		{
			if (response.status == 204)
			{
				if (!this.articles[iIndex].attributes.is_read)
				{
					this.$dispatch('feeds.updateCount', {
						'feed_id': this.articles[iIndex].attributes.feed_id,
						'counter_name': 'count_is_unread',
						'diff': -1
					});
				}

				this.$dispatch('feeds.updateCount', {
					'feed_id': this.articles[iIndex].attributes.feed_id,
					'counter_name': 'count_articles',
					'diff': -1	
				});

				this.articles.splice(iIndex, 1);

				// Select next article only if one already selected
				if (this.iSelectedIndex > -1)
					this.select(iIndex);
			}
		});	
	},

	delete(iIndex)
	{
		if (!(iIndex in this.articles))
			return;
		
		utils._fetch('DELETE', 'articles/'+ this.articles[iIndex].id)
		.then((response) =>
		{
			if (response.status == 204)
			{
				if (!this.articles[iIndex].attributes.is_read)
				{
					this.$dispatch('feeds.updateCount', {
						'feed_id': this.articles[iIndex].attributes.feed_id,
						'counter_name': 'count_is_unread',
						'diff': -1
					});
				}

				this.$dispatch('feeds.updateCount', {
					'feed_id': this.articles[iIndex].attributes.feed_id,
					'counter_name': 'count_articles',
					'diff': -1	
				});

				this.articles.splice(iIndex, 1);

				// Select next article only if one already selected
				if (this.iSelectedIndex > -1)
					this.select(iIndex);
			}
		});
	},

	getDomain(sUrl)
	{
		try
		{
			const hostname = new URL(sUrl).hostname;
			return hostname.replace(/^www\./, '');
		}
		catch (e) { return ''; }
	}
}