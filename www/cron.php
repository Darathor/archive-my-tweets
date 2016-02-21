<?php
// Tested only on PHP 5.5.
if (version_compare(phpversion(), '5.5.0') < 0)
{
	exit('AMT requires PHP 5.5.0 or higher. Your server is running PHP ' . phpversion() . '.');
}

// AMT requires 64-bit system.
if (PHP_INT_SIZE < 8)
{
	exit('AMT 64-bit system.');
}

// Initialization.
define('ROOT_DIR', dirname(__DIR__));
if (!file_exists(ROOT_DIR . '/config/config.php'))
{
	die('Missing config/config.php file. Copy config/config.example.php to config/config.php and customize the settings' . PHP_EOL);
}

// Run.
/** @var array $config */
require_once(ROOT_DIR . '/includes.php');

// Check call context.
$isCLI = (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']));
$isWeb = (isset($_GET['secret']) && $_GET['secret'] == TWITTER_CRON_SECRET);
if (!$isCLI && !$isWeb)
{
	echo 'Not authorized.', PHP_EOL;
	exit(1);
}
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'all';
$amt = new \Darathor\Amt\App($core);

// API tweets.
if ($mode === 'all' || $mode === 'timeline')
{
	$archiveOutput = 'Importing timeline...' . PHP_EOL;
	$archiveOutput .= $amt->archiveTimeline();
	if ($isWeb)
	{
		echo '<pre>' . $archiveOutput . '</pre>';
	}
	else
	{
		echo $archiveOutput;
	}
}

// API favorites.
if ($mode === 'all' || $mode === 'favorites')
{
	$archiveOutput = 'Importing favorites...' . PHP_EOL;
	$archiveOutput .= $amt->archiveFavorites();
	if ($isWeb)
	{
		echo '<pre>' . $archiveOutput . '</pre>';
	}
	else
	{
		echo $archiveOutput;
	}
}

// Import JSON from an official twitter archive monthly .js files should be in a folder called 'json'.
if ($mode === 'all' || $mode === 'json')
{
	$importOutput = $amt->importJSON(ROOT_DIR . '/json');
	if ($isWeb)
	{
		echo '<pre>' . $importOutput . '</pre>';
	}
	else
	{
		echo $importOutput;
	}
}