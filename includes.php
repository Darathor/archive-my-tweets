<?php
require_once ROOT_DIR . '/core/Core.php';
require_once ROOT_DIR . '/amt/App.php';
require_once ROOT_DIR . '/vendor/tijsverkoyen/TwitterOAuth/Twitter.php';
require_once ROOT_DIR . '/vendor/tijsverkoyen/TwitterOAuth/Exception.php';

if (file_exists(ROOT_DIR . '/config/config.php'))
{
	require_once ROOT_DIR . '/config/config.php';
	$core = new \Darathor\Core\Core([
		'twitter' => [
			'username' => TWITTER_USERNAME,
			'name' => TWITTER_NAME,
			'id' => TWITTER_ID
		],
		'auth' => [
			'consumerKey' => TWITTER_CONSUMER_KEY,
			'consumerSecret' => TWITTER_CONSUMER_SECRET,
			'oauthToken' => TWITTER_OAUTH_TOKEN,
			'oauthSecret' => TWITTER_OAUTH_SECRET
		],
		'db' => [
			'host' => DB_HOST,
			'username' => DB_USERNAME,
			'password' => DB_PASSWORD,
			'database' => DB_NAME,
			'prefix' => DB_TABLE_PREFIX
		],
		'baseUrl' => BASE_URL,
		'cronSecret' => TWITTER_CRON_SECRET,
		'theme' => defined('AMT_THEME') ? AMT_THEME : 'default',
		'LCID' => defined('AMT_LCID') ? AMT_LCID : 'fr_FR',
		'timezone' => defined('AMT_TIMEZONE') ? AMT_TIMEZONE : 'Europe/Paris'
	]);
}

