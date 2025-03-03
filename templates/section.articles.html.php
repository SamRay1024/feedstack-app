<section id="articles" class="paxs" x-data="articles">
	<div class="tce big mtl" style="color:var(--color-gray);"
		x-show="!iFeedId"><i class="ti ti-click"></i><br /><?= __('Select a feed') ?></div>

	<div class="tce mtl" style="color:var(--color-gray);"
		x-show="iFeedId && !articles.length"><i class="ti ti-mood-sad"></i><br /><?= __('No article at this time.') ?></div>

	<ul id="articles-list" class="list pan clear">
		<template x-for="(art, artIndex) in articles">
			<li @click="select(artIndex)"
				:class="{'current': artIndex == iSelectedIndex, 'new': art.attributes.is_new, 'unread': !art.attributes.is_read}">

				<i class="bullet right"
					@click.stop="markAsRead(artIndex, !art.attributes.is_read)"></i>

				<span class="art-title mbxs"
					x-text="art.attributes.title"></span>

				<span class="meta" x-show="$store.view != ''">
					<i class="ti ti-rss"></i>
					<span x-text="getDomain(art.attributes.link)"></span><br />
				</span>

				<span class="meta">
					<i class="ti ti-calendar"></i>
					<span x-text="art.attributes.pub_date"></span>
					<span x-show="art.attributes.author">
						<i class="ti ti-user"></i>
						<span x-text="art.attributes.author"></span>
					</span>
				</span>

				<span class="art-actions hidden small">
					<span @click="markForLater(artIndex)"><i class="ti"
						:class="{'ti-pin': !art.attributes.is_read_later, 'ti-pin-filled': art.attributes.is_read_later}"></i></span>
					
					<span x-data="archiveArticle(artIndex)">
						<i class="ti ti-check" title="<?= __('Confirm') ?>" x-bind="confirm"></i>
						<i class="ti ti-archive" title="<?= __('Archive article') ?>" x-bind="trigger"></i>
					</span>

					<span x-data="deleteArticle(artIndex)">
						<i class="ti ti-check" title="<?= __('Confirm') ?>" x-bind="confirm"></i>
						<i class="ti ti-trash" title="<?= __('Delete article') ?>" x-bind="trigger"></i>
					</span>
				</span>
			</li>
		</template>
	</ul>
</section>