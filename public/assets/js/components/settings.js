export let settings = {

	bOpened: false,

	get updateInterval() { return Alpine.store('settings').updateInterval; },
	set updateInterval(value) { Alpine.store('settings').updateInterval = value; },
	
	init()
	{
		window.addEventListener('settings.open', () => { this.open(); });
	},

	open() { this.bOpened = true; },
	close() { this.bOpened = false; },

	save()
	{
		Alpine.store('sys')
			.fetch('PUT', 'params/update_interval', JSON.stringify({ value: this.updateInterval }))
			.then(response => {
				if (response.data) {
					this.close();
					this.$dispatch('feeds.updateInterval', this.updateInterval);
				}
			});
	}
};