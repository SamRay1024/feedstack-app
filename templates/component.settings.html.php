<template x-data="settings" x-if="bOpened">
	<div class="modal">
		<form class="modal-box w50" method="post" @submit.prevent="save">
			<div class="modal-header h3">
				<?= __('Settings') ?>
			</div>
			<div class="modal-body wform-aligned">
				<div class="wfield">
					<label for="updateInterval"><?= __('Update interval :') ?></label>
					<div class="winput">
						<input type="number" id="updateInterval" name="update_interval"
							x-model="updateInterval" min="5" max="1440" step="5">
						<?= __('minutes') ?><br />
						<small class="meta"><?= __('5 minutes minimum, 24 hours maximum') ?></small>
					</div>
				</div>
			</div>
			<div class="modal-footer tri">
				<button class="left" @click="close()"><?= __('Cancel') ?></button>
				<button class="wbtn-primary" type="submit"><?= __('Save') ?></button>
			</div>
		</form>
	</div>
</template>