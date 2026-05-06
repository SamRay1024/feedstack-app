<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>FeedStack</title>

	<link rel="stylesheet" media="screen" href="/assets/css/w.css?<?= time() ?>">
	<link rel="stylesheet" media="screen" href="/assets/css/wrss.css?<?= time() ?>">
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@2.36.0/tabler-icons.min.css">

	<script>
		const BASE_URL = '//<?= $sHttpHost ?>';
		const LOGOUT_URI = '<?= $sLogoutUrl ?>';
	</script>
	<script src="/assets/js/app.js?<?= time() ?>" type="module"></script>
	<script src="/assets/js/alpine.min.js" defer></script>
</head>

<body>
	<div class="app">

		<!-- == FEEDS LIST ================================================= -->
		<?= $this->render('section.feeds') ?>

		<!-- == ARTICLES LIST ============================================== -->
		<?= $this->render('section.articles') ?>

		<!-- == ARTICLE VIEW =============================================== -->
		<?= $this->render('section.article') ?>
	</div>

	<!-- == GENERIC CONFIRM MODAL ========================================== -->
	<?= $this->render('component.confirm') ?>
	<?= $this->render('component.settings') ?>
</body>

</html>