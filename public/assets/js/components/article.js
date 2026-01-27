export let article = {
	current: false,
	iCurrentIndex: -1,
	scrollListener: null,

	init()
	{
		window.addEventListener('article.show', (event) =>
		{
			this.current = event.detail.article;
			this.iCurrentIndex = (event.detail.index >= 0 ? event.detail.index : -1);
			this.setupScrollListener();
		});
	},

	setupScrollListener()
	{
		if (this.scrollListener)
			document.getElementById('article')?.removeEventListener('scroll', this.scrollListener);

		this.scrollListener = () => 
		{
			const articleElement = document.getElementById('article');
			const toolbarElement = document.querySelector('.article-toolbar');

			if (articleElement && toolbarElement)
			{
				if (articleElement.scrollTop > 0)
					toolbarElement.classList.add('shadow');
				else
					toolbarElement.classList.remove('shadow');
			}
		};

		document.getElementById('article')?.addEventListener('scroll', this.scrollListener);
	},

	prev()
	{
		this.$dispatch('articles.select', --this.iCurrentIndex);
	},
	
	next()
	{
		this.$dispatch('articles.select', ++this.iCurrentIndex);
	},

	readLater()
	{
		this.$dispatch('articles.markForLater', this.iCurrentIndex);
	},

	restore()
	{
		this.$dispatch('articles.restore', this.iCurrentIndex);
	}
}