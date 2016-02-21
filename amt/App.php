<?php
namespace Darathor\Amt;

require_once ROOT_DIR . '/amt/Router.php';
require_once ROOT_DIR . '/amt/Model.php';
require_once ROOT_DIR . '/amt/View.php';
require_once ROOT_DIR . '/amt/Paginator.php';
require_once ROOT_DIR . '/amt/Controller.php';
require_once ROOT_DIR . '/amt/Importer.php';
require_once ROOT_DIR . '/amt/Archiver.php';
require_once ROOT_DIR . '/amt/Tweet.php';
require_once ROOT_DIR . '/amt/Avatar.php';

require_once ROOT_DIR . '/amt/Entities/Entity.php';
require_once ROOT_DIR . '/amt/Entities/Image.php';
require_once ROOT_DIR . '/amt/Entities/Link.php';
require_once ROOT_DIR . '/amt/Entities/Hashtag.php';
require_once ROOT_DIR . '/amt/Entities/Mention.php';
require_once ROOT_DIR . '/amt/Entities/Video.php';

/**
 * The ArchiveMyTweets application class.
 */
class App
{
	protected $core;
	protected $model;
	protected $view;
	protected $controller;
	protected $router;

	// current version
	const VERSION = '0.1';

	/**
	 * @param \Darathor\Core\Core $core
	 * @throws \Exception
	 */
	public function __construct($core)
	{
		$this->core = $core;
		$configuration = $core->getConfiguration();
		$i18n = $core->getI18n();

		// Model.
		try
		{
			$dbConfig = $configuration->get('db');
			$dsn = 'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['database'] . ';charset=utf8';
			$db = new \PDO($dsn, $dbConfig['username'], $dbConfig['password']);
			$this->model = new \Darathor\Amt\Model($db, $dbConfig['prefix'], $configuration->get('twitter', 'id'));
		}
		catch (\PDOException $e)
		{
			throw $e;
		}

		// View.
		try
		{
			$this->view = new \Darathor\Amt\View(ROOT_DIR . '/themes/' . $configuration->get('theme'), $i18n, $configuration);
		}
		catch (\Exception $e)
		{
			throw $e;
		}

		// Paginator.
		try
		{
			$this->paginator = new \Darathor\Amt\Paginator($this->view);
		}
		catch (\Exception $e)
		{
			throw $e;
		}

		// Controller
		$controllerData = [
			'config' => [
				'twitter' => $configuration->get('twitter'),
				'system' => [
					'theme' => $configuration->get('theme'),
					'baseUrl' => $configuration->get('baseUrl')
				]
			]
		];
		$this->controller = new \Darathor\Amt\Controller($this->model, $this->view, $this->paginator, $i18n, $controllerData);
		$this->router = new \Darathor\Amt\Router($this->controller);
	}

	/**
	 * Returns the core
	 */
	public function getCore()
	{
		return $this->core;
	}

	/**
	 * Runs the web interface
	 * @param string $pathInfo For example: "/archive/2016/05/15/"
	 */
	public function run($pathInfo)
	{
		$this->router->route($pathInfo);
	}

	/**
	 * Grabs all the latest tweets and puts them into the database.
	 *
	 * @return string Returns a string with informational output.
	 */
	public function archiveTimeline()
	{
		$configuration = $this->core->getConfiguration();
		$authConfig = $configuration->get('auth');

		// create twitter instance
		$twitter = new \TijsVerkoyen\Twitter\Twitter($authConfig['consumerKey'], $authConfig['consumerSecret']);
		$twitter->setOAuthToken($authConfig['oauthToken']);
		$twitter->setOAuthTokenSecret($authConfig['oauthSecret']);

		$archiver = new \Darathor\Amt\Archiver($configuration->get('twitter', 'username'), $twitter, $this->model);
		return $archiver->archive(\Darathor\Amt\Archiver::TYPE_TIMELINE);
	}

	/**
	 * Grabs all the latest favorites and puts them into the database.
	 *
	 * @return string Returns a string with informational output.
	 */
	public function archiveFavorites()
	{
		$configuration = $this->core->getConfiguration();
		$authConfig = $configuration->get('auth');

		// create twitter instance
		$twitter = new \TijsVerkoyen\Twitter\Twitter($authConfig['consumerKey'], $authConfig['consumerSecret']);
		$twitter->setOAuthToken($authConfig['oauthToken']);
		$twitter->setOAuthTokenSecret($authConfig['oauthSecret']);

		$archiver = new \Darathor\Amt\Archiver($configuration->get('twitter', 'username'), $twitter, $this->model);
		return $archiver->archive(\Darathor\Amt\Archiver::TYPE_FAVORITES);
	}

	/**
	 * Imports tweets from the JSON files in a downloaded Twitter Archive
	 *
	 * @param string $directory The directory to look for Twitter .js files.
	 * @return string Returns a string with informational output.
	 */
	public function importJSON($directory)
	{
		$importer = new \Darathor\Amt\Importer();
		return $importer->importJSON($directory, $this->model);
	}
}