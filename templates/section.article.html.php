<section id="article" class="pam" x-data="article">
	<template x-if="current">
		<div>
			<div class="article-toolbar mbs">
				<i class="wbtn ti ti-chevron-up" title="<?= __('Show previous article') ?>"
					@click="prev()"></i>
				<i class="wbtn ti ti-chevron-down" title="<?= __('Show next article') ?>"
					@click="next()"></i>

				<span @click="readLater"><i class="wbtn ti"
					title="<?= __('Read later') ?>"
					:class="{'ti-pin': !current.attributes.is_read_later, 'ti-pin-filled': current.attributes.is_read_later}"></i></span>

				<span x-data="archiveArticle()" x-show="!current.attributes.is_archive">
					<i class="wbtn ti ti-check" title="<?= __('Confirm') ?>" x-bind="confirm"></i>
					<i class="wbtn ti ti-archive" title="<?= __('Archive article') ?>" x-bind="trigger"></i>
				</span>

				<span x-data="deleteArticle()" x-show="!current.attributes.deleted_at">
					<i class="wbtn ti ti-check" title="<?= __('Confirm') ?>" x-bind="confirm"></i>
					<i class="wbtn ti ti-trash" title="<?= __('Delete article') ?>" x-bind="trigger"></i>
				</span>

				<span @click="restore" x-show="current.attributes.deleted_at"><i class="wbtn ti ti-restore"
					title="<?= __('Restore') ?>"></i></span>

				<span x-data="emptyArticle()" x-show="current.attributes.deleted_at">
					<i class="wbtn ti ti-check" title="<?= __('Confirm') ?>" x-bind="confirm"></i>
					<i class="wbtn ti ti-trash-x" title="<?= __('Remove from trash') ?>" x-bind="trigger"></i>
				</span>
			</div>
			<header class="pam pts">
				<h1 class="biggest" x-text="current.attributes.title"></h1>

				<span class="meta">
					<i class="ti ti-calendar"></i>
					<span x-text="current.attributes.pub_date"></span>

					<span x-show="current.attributes.author">
						<i class="ti ti-user"></i>
						<span x-text="current.attributes.author"></span>
					</span>

					<br>

					<i class="ti ti-link"></i>
					<a target="_blank" :href="current.attributes.link"
						x-text="current.attributes.link_short"></a>
				</span>
			</header>

			<article class="pam" x-html="current.attributes.content"></article>
		</div>
	</template>
</section>