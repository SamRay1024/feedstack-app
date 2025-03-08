import * as utils from '../utils.js';

export let views = {
	init()
	{
		this.reloadCounts();

		window.addEventListener('views.reloadCounts', () =>
		{
			this.reloadCounts();
		});
	},

	reloadCounts()
	{
		this.$store.sys.fetch('GET', 'globals/counts').then(response =>
		{
			if (response.data)
			{
				this.$store.counts = response.data;	
			}
		});
	},

	select(sView)
	{
		this.$dispatch('articles.load.view', {view: sView});
	}
}