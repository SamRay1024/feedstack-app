import * as utils from '../utils.js';

export let views = {
	sCurrentView: '',
	counts: {
		'today': 0, 'today_unread': 0,
		'later': 0, 'later_unread': 0,
		'archives': 0
	},

	init()
	{
		this.reloadCounts();

		window.addEventListener('views.updateCount', (event) =>
		{
			this.counts[event.detail.view] = event.detail.count;
		});

		window.addEventListener('views.unselect', () => { this.sCurrentView = ''; });
	},

	reloadCounts()
	{
		utils._fetch('GET', 'globals/counts').then(response =>
		{
			if (response.data)
			{
				this.counts = response.data;	
			}
		});
	},

	select(sView)
	{
		this.sCurrentView = sView;
		this.$dispatch('feeds.unselect');
		this.$dispatch('articles.load.view', {view: sView});
	}
}