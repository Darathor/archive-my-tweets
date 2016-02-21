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

// Run.
define('ROOT_DIR', dirname(__DIR__));
if (file_exists(ROOT_DIR . '/config/config.php'))
{
	require_once(ROOT_DIR . '/includes.php');
	$amt = new \Darathor\Amt\App($core);
	$amt->run(isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '');
}
else
{
	require_once(ROOT_DIR . '/amt/installer.php');
	$installer = new \Darathor\Amt\Installer(ROOT_DIR . '/config');
	$installer->run();
}