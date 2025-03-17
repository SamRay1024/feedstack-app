export let article = {
	current: false,
	iCurrentIndex: -1,

	init()
	{
		window.addEventListener('article.show', (event) =>
		{
			this.current = event.detail.article;
			this.iCurrentIndex = (event.detail.index >= 0 ? event.detail.index : -1);
		});
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