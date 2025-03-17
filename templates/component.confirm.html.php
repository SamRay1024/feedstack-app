<div x-data="confirm" x-show="open" x-cloak>
	<div class="modal">
		<div class="modal-box w30">
			<div class="modal-header h3" x-text="title"></div>
			<div class="modal-body">
				<p x-html="message"></p>
			</div>
			<div class="modal-footer tri">
				<button class="left" @click="cancel"><?= __('Cancel') ?></button>
				<button class="wbtn-primary" @click="confirm"><?= __('Confirm') ?></button>
			</div>
		</div>
	</div>
</div>