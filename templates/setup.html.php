<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>FeedStack Setup</title>

	<link rel="stylesheet" media="screen" href="/assets/css/w.css?<?= time() ?>">
	<link rel="stylesheet" media="screen" href="/assets/css/wrss.css?<?= time() ?>">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.36.0/tabler-icons.min.css">

	<style>
		body {
			background: #f5f5f5;
		}

		form {
			background: #fff;
			border-radius: var(--border-radius);
			box-shadow: 0px 0px 3px rgba(0, 0, 0, 0.1);
		}
	</style>

	<script src="/assets/js/alpine.min.js" defer></script>
	<script>
	document.addEventListener('alpine:init', () =>
	{
		Alpine.data('setup', () => ({
			submit: 'disabled',
			generateRandomPassword()
			{
				const length = 12;
				const charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()";
				let password = "";
				for (let i = 0, n = charset.length; i < length; ++i) {
					password += charset.charAt(Math.floor(Math.random() * n));
				}
				return password;
			},
			validateForm(event)
			{
				console.log(event.target.value);
			}
		}));
	});
	</script>
</head>

<body>
	<header class="center mtl tce">
		<h1><i class="ti ti-settings-cog"></i><br /><?= $appname ?> Setup</h1>
	</header>

	<section class="center w600p mtl mbl">

		<?php if (isset($errors) && count($errors)) : ?>
		<div class="wcallout wdanger mbl">
			<h5><?= __('Unable to install, please fix the following error(s) :') ?></h5>

			<ul>
				<?php foreach ($errors as $error) : ?>
				<li><?= $error ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<?php if (isset($success) && $success) : ?>
			<p class="wcallout wsuccess mbl"><?= $success ?></p>
		<?php endif; ?>

		<form method="post" class="pal wform-aligned" x-data="setup">
			<?php // ==== PERMISSIONS ====================================== ?>
			<?php if ($screen == 'paths_rights') : ?>
			<h4><?= __('Adjust permissions') ?></h4>

			<p class="wcallout wdanger"><?= __(
				'Please set all the following folders writeable '
				.'to continue the installation.'
			) ?></p>

			<table class="w100">
				<thead><tr>
					<th><?= __('Folder') ?></th>
					<th><?= __('Writeable') ?></th>
				</tr></thead>
				<tbody>
					<tr>
						<td><small><code><?= config('app.storage_path') ?></code></small></td>
						<td class="tce"><i class="ti ti-<?= $storage_ok ? 'check' : 'x' ?>"></i></td>
					</tr>
					<tr>
						<td><small><code><?= config('app.cache_path') ?></code></small></td>
						<td class="tce"><i class="ti ti-<?= $cache_ok ? 'check' : 'x' ?>"></i></td>
					</tr>
					</tr>
					<tr>
						<td><small><code><?= config('app.logs_path') ?></code></small></td>
						<td class="tce"><i class="ti ti-<?= $logs_ok ? 'check' : 'x' ?>"></i></td>
					</tr>
				</tbody>
			</table>

			<p><a href="." class="wbtn wbtn-green"><?= __('Ok, it\'s done') ?></a></p>
			<?php endif; ?>

			<?php // ==== SETUP FORM ======================================== ?>
			<?php if ($screen == 'setup_form') : ?>

			<h4><i class="ti ti-database"></i> <?= __('Database') ?></h4>

			<div x-data="{mysql: <?= post('db_driver') == 'mysql' ? 'true' : 'false' ?>}">

				<div class="wfield">
					<span class="label"><?= __('Type:') ?></span>
					<div class="winput inline">
						<label><input type="radio" name="db_driver" value="sqlite"
							<?= checked(post('db_driver', 'sqlite'), 'sqlite') ?>
							@click="mysql=false">SQLite</label>
						<label class="mlm"><input type="radio" name="db_driver" value="mysql"
							<?= checked(post('db_driver'), 'mysql') ?>
							@click="mysql=true">MySQL</label>
					</div>
				</div>

				<div class="wfield" x-show="mysql">
					<label for="db_host"><?= __('Host:') ?></label>
					<div class="winput">
						<input type="text" name="db_host" id="db_host"
							value="<?= post('db_host', 'localhost') ?>">
					</div>
				</div>
				<div class="wfield" x-show="mysql">
					<label for="db_port"><?= __('Port:') ?></label>
					<div class="winput">
						<input type="text" name="db_port" id="db_port"
							value="<?= post('db_port', '3306') ?>" size="5">
					</div>
				</div>
				<div class="wfield" x-show="mysql">
					<label for="db_username"><?= __('Username:') ?></label>
					<div class="winput">
						<input type="text" name="db_username" id="db_user"
							value="<?= post('db_user') ?>">
					</div>
				</div>
				<div class="wfield" x-show="mysql">
					<label for="db_pwd"><?= __('Password:') ?></label>
					<div class="winput">
						<input type="text" name="db_pwd" id="db_pwd"
							value="<?= post('db_pwd') ?>">
					</div>
				</div>
				<div class="wfield" x-show="mysql">
					<label for="db_database"><?= __('Database:') ?></label>
					<div class="winput">
						<input type="text" name="db_database" id="db_database"
							value="<?= post('db_database') ?>">
					</div>
				</div>
			</div>

			<h4><i class="ti ti-user-plus"></i> <?= __('User account') ?></h4>

			<div class="wfield">
				<label for="user_name"><?= __('Name:') ?></label>
				<div class="winput">
					<input type="text" name="user_name" id="user_name"
						value="<?= post('user_name') ?>">
				</div>
			</div>

			<div class="wfield">
				<label for="user_email"><?= __('Email:') ?></label>
				<div class="winput">
					<input type="text" name="user_email" id="user_email"
						class="w100" value="<?= post('user_email') ?>">
				</div>
			</div>

			<div class="wfield" x-data="{password: '<?= post('user_pwd') ?>'}">
				<label for="user_pwd"><?= __('Password:') ?></label>
				<div class="winput">
					<input type="text" name="user_pwd" id="user_pwd"
						value="<?= post('user_pwd') ?>"
						x-model="password">
					<a class="wbtn wbtn-blue mts"
						@click="password = generateRandomPassword()">
						<i class="ti ti-arrows-shuffle"></i> <?= __('Generate') ?></a>
					<a class="wbtn mts"
						@click="navigator.clipboard.writeText(password)">
						<i class="ti ti-copy"></i> <?= __('Copy') ?></a>
				</div>
			</div>

			<h4><i class="ti ti-adjustments"></i> <?= __('Settings') ?></h4>

			<div class="wfield">
				<label for="timezone"><?= __('Timezone:') ?></label>
				<div class="winput">
					<input type="text" name="timezone" id="timezone"
						value="<?= post('timezone', 'Europe/Paris') ?>">
				</div>
			</div>

			<div class="wfield">
				<label for="i18n_locale"><?= __('Language:') ?></label>
				<div class="winput inline">
					<label><input type="radio" name="i18n_locale" value="fr_FR"
						<?= checked(post('i18n_locale', config('app.i18n_locale')), 'fr_FR') ?>>FR</label>
					<label><input type="radio" name="i18n_locale" value="en_EN"
						<?= checked(post('i18n_locale'), 'en_EN') ?>>EN</label>
				</div>
			</div>

			<div x-data="{can_register: <?= post('can_register') == '1' ? 'true' : 'false' ?>}">

				<div class="wfield">
					<label for="can_register"><?= __('Registrations:') ?></label>
					<div class="winput inline">
						<label><input type="radio" name="can_register" value="0"
							<?= checked(post('can_register', '0'), '0') ?>
							@click="can_register=false"><?=
								__('Closed') ?></label>
						<label><input type="radio" name="can_register" value="1"
							<?= checked(post('can_register'), '1') ?>
							@click="can_register=true"><?= 
								__('Opened') ?></label>
					</div>
				</div>

				<div class="mtm" x-show="can_register">

					<h4><i class="ti ti-mail-cog"></i> <?= __('Mailer configuration') ?></h4>
	
					<div x-data="{smtp: <?= post('mailer_driver') == 'smtp' ? 'true' : 'false' ?>}">
						<div class="wfield">
							<label for="mailer_driver"><?= __('Driver:') ?></label>
							<div class="winput">
								<label><input type="radio" name="mailer_driver" value="mail" @click="smtp=false"
									<?= (post('mailer_driver', 'mail') == 'mail' ? 'checked' : '') ?>><?= __('<code>mail()</code> function') ?></label>
								<label><input type="radio" name="mailer_driver" value="sendmail" @click="smtp=false"
									<?= (post('mailer_driver') == 'sendmail' ? 'checked' : '') ?>><?= __('<code>sendmail()</code> function') ?></label>
								<label><input type="radio" name="mailer_driver" value="smtp" @click="smtp=true"
									<?= (post('mailer_driver') == 'smtp' ? 'checked' : '') ?>>SMTP</label>
							</div>
						</div>
						
						<div class="wfield" x-show="smtp">
							<label for="mailer_smtp_host"><?= __('SMTP host:') ?></label>
							<div class="winput">
								<input type="text" name="mailer_smtp_host" id="mailer_smtp_host"
									class="w100" value="<?= post('mailer_smtp_host') ?>">
							</div>
						</div>

						<div class="wfield" x-show="smtp">
							<label for="mailer_smtp_port"><?= __('SMTP port:') ?></label>
							<div class="winput">
								<input type="text" name="mailer_smtp_port" id="mailer_smtp_port"
									value="<?= (int) post('mailer_smtp_port') ?>" size="5">
							</div>
						</div>
						
						<div class="wfield" x-show="smtp">
							<label for="mailer_smtp_username"><?= __('SMTP username:') ?></label>
							<div class="winput">
								<input type="text" name="mailer_smtp_username" id="mailer_smtp_username"
								value="<?= post('mailer_smtp_username') ?>">
							</div>
						</div>
						
						<div class="wfield" x-show="smtp">
							<label for="mailer_smtp_password"><?= __('SMTP password:') ?></label>
							<div class="winput">
								<input type="text" name="mailer_smtp_password" id="mailer_smtp_password"
									value="<?= post('mailer_smtp_password') ?>">
							</div>
						</div>
					</div>

					<div class="wfield">
						<label for="mailer_from"><?= __('From address:') ?></label>
						<div class="winput">
							<input type="text" name="mailer_from" id="mailer_from"
								class="w100" value="<?= post('mailer_from') ?>"
								placeholder="noreply@myapp.com">
						</div>
					</div>

					<div class="wfield">
						<label for="mailer_replyto"><?= __('Reply-To address:') ?></label>
						<div class="winput">
							<input type="text" name="mailer_replyto" id="mailer_replyto"
								class="w100" value="<?= post('mailer_replyto') ?>"
								placeholder="support@myapp.com">
						</div>
					</div>

				</div>
			</div>

			<div class="wfield tce mtl">
				<button class="wbtn-green big" name="install"><?= __('Install') ?></button>
			</div>
			<?php endif; ?>

			<?php // ==== .ENV WRITE ERROR ================================== ?>
			<?php if ($screen == 'env_error') : ?>
			
			<h4><?= __('Almost ready') ?></h4>

			<p><?= __('I was not able to save your configuration in the .env file in the root folder.') ?></p>

			<p><?= __('You can copy-paste manually the following content to finish the installation process :') ?></p>

			<p><textarea readonly="readonly" class="w100" rows="20"><?= $env_content ?></textarea></p>

			<p><a href="." class="wbtn wbtn-green"><?= __('Ok, it\'s done') ?></a></p>
			<?php endif; ?>

			<?php // ==== SUCCESS =========================================== ?>
			<?php if ($screen == 'success') : ?>

			<h4><?= __('Great job !') ?></h4>

			<p><?= __('You\'ve done the harder part. Installation is finished !') ?></p>

			<p><a href="." class="wbtn wbtn-green"><?= __('Go and see how it looks like !') ?></a></p>
			
			<?php endif; ?>
			
			<input type="hidden" name="_token" value="<?= $token ?>">
		</form>
	</section>
</body>

</html>