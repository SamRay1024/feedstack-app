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
					<span @click="markForLater(artIndex)"><i class="wbtn ti"
						:class="{'ti-pin': !art.attributes.is_read_later, 'ti-pin-filled': art.attributes.is_read_later}"></i></span>
					
					<span x-data="archiveArticle(artIndex)" x-show="!art.attributes.is_archive">
						<i class="wbtn ti ti-check" title="<?= __('Confirm') ?>" x-bind="confirm"></i>
						<i class="wbtn ti ti-archive" title="<?= __('Archive article') ?>" x-bind="trigger"></i>
					</span>

					<span x-data="deleteArticle(artIndex)" x-show="!art.attributes.deleted_at">
						<i class="wbtn wbtn-red ti ti-check" title="<?= __('Confirm') ?>" x-bind="confirm"></i>
						<i class="wbtn ti ti-trash" title="<?= __('Delete article') ?>" x-bind="trigger"></i>
					</span>

					<span @click="restore(artIndex)" x-show="art.attributes.deleted_at"><i
						class="ti ti-restore"
						title="<?= __('Restore') ?>"></i></span>

					<span x-data="emptyArticle(artIndex)" x-show="art.attributes.deleted_at">
						<i class="ti ti-check" title="<?= __('Confirm') ?>" x-bind="confirm"></i>
						<i class="ti ti-trash-x" title="<?= __('Remove from trash') ?>" x-bind="trigger"></i>
					</span>
				</span>
			</li>
		</template>
	</ul>
</section>