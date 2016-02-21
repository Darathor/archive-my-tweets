<?php
// Global variables.
/** @var $i18n \Darathor\Core\I18n */
$i18n = $this->i18n;
/** @var $config \Darathor\Core\Configuration */
$config = $this->configuration;
$baseUrl = $config->get('system', 'baseUrl');
$screenName = $config->get('twitter', 'name');
$userName = $config->get('twitter', 'username');

// Specific variables.
/** @var $content string|null */
/** @var $pageType string|null */
/** @var $searchTerm string|null */
?>
<!DOCTYPE html>
<html lang="<?php echo substr($config->get('LCID'), 0, 2); ?>">
<head>
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>@<?php echo $userName; ?> - <?php echo $screenName; ?> - <?php echo $i18n->trans('tweets', ['ucf']); ?></title>
	<base href="<?php echo $baseUrl; ?>index.php/" />
	<link href="theme/lib/bootstrap-3.3.6/css/bootstrap.min.css" rel="stylesheet" />
	<link href="theme/lib/bootstrap-3.3.6/css/bootstrap-theme.min.css" rel="stylesheet" />
	<link href="theme/styles.css" rel="stylesheet" />
</head>
<body class="amt-<?php echo $pageType; ?>">
	<div class="navbar navbar-inverse navbar-fixed-top">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#main-navbar-collapse" aria-expanded="false">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="<?php echo $baseUrl; ?>">
					<img class="avatar" src="<?php echo $baseUrl; ?>img/avatar.png" />
				</a>
				<a class="navbar-brand" href="<?php echo $baseUrl; ?>">
					<?php echo $userName; ?> <small>@<?php echo $screenName; ?></small>
				</a>
			</div>
			<div class="collapse navbar-collapse" id="main-navbar-collapse">
				<form action="search/" class="navbar-form navbar-right" method="get" role="search">
					<div class="form-group">
						<input type="text" size="20" name="q" value="<?php echo (isset($search) && $search) ? htmlentities($searchTerm) : ''; ?>"
							class="form-control" placeholder="<?php echo $i18n->trans('search_action', ['ucf']); ?>" />
					</div>
					<button type="submit" class="btn btn-default btn-icon" title="<?php echo $i18n->trans('search_action', ['ucf']); ?>">
						<span class="glyphicon glyphicon-search" aria-hidden=""></span>
					</button>
				</form>
			</div>
		</div>
	</div>

	<div class="container">
		<div class="row">
			<?php echo $content; ?>
		</div>
	</div>

	<div class="footer" id="footer">
		<div class="container">
			<strong><?php echo $i18n->trans('powered_by', ['ucf']); ?> <a href="TODO"><?php echo $i18n->trans('application_name'); ?></a> <?php echo $i18n->trans('developed_by'); ?> <a href="http://wp.darathor.com">Darathor</a></strong>
			<br />
			<small>
				<?php echo $i18n->trans('forked_from', ['ucf']); ?> <a href="http://amwhalen.com/projects/archive-my-tweets/">Archive My Tweets</a> <?php echo $i18n->trans('by'); ?> <a href="http://amwhalen.com">Andrew M. Whalen</a></small>
		</div>
	</div>

	<script src="theme/lib/jquery-2.2.0/jquery.min.js"></script>
	<script src="theme/lib/bootstrap-3.3.6/js/bootstrap.min.js"></script>
</body>
</html>
