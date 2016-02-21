<?php
namespace Darathor\Amt;

/**
 * @name \Darathor\Amt\Controller
 */
class Controller
{
	/**
	 * @var \Darathor\Amt\Model
	 */
	protected $model;

	/**
	 * @var \Darathor\Amt\View
	 */
	protected $view;

	/**
	 * @var \Darathor\Amt\Paginator
	 */
	protected $paginator;

	/**
	 * @var \Darathor\Core\I18n
	 */
	protected $i18n;

	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @param \Darathor\Amt\Model $model
	 * @param \Darathor\Amt\View $view
	 * @param \Darathor\Amt\Paginator $paginator
	 * @param \Darathor\Core\I18n $i18n
	 * @param array $data
	 */
	public function __construct(\Darathor\Amt\Model $model, \Darathor\Amt\View $view, \Darathor\Amt\Paginator $paginator, \Darathor\Core\I18n $i18n, array $data = [])
	{
		$this->model = $model;
		$this->view = $view;
		$this->paginator = $paginator;
		$this->i18n = $i18n;
		$this->data = $data;
	}

	/**
	 * @param string $path
	 */
	public function resource($path)
	{
		$path = ROOT_DIR . '/themes/' . $this->data['config']['system']['theme'] . str_replace('..', '__', $path);
		if (file_exists($path))
		{
			$extension = substr($path, strrpos($path, '.') + 1);
			switch ($extension)
			{
				case 'js':
					$header = 'Content-Type: application/javascript';
					break;

				case 'css':
					$header = 'Content-Type: text/css';
					break;

				// Images.
				case 'gif':
					$header = 'Content-Type: image/gif';
					break;

				case 'jpg':
				case 'jpeg':
					$header = 'Content-Type: image/jpeg';
					break;

				case 'png':
					$header = 'Content-Type: image/png';
					break;

				case 'svg':
					$header = 'Content-Type: image/svg+xml';
					break;

				// Fonts.
				case 'ttf':
				case 'otf':
					$header = 'Content-Type: application/font-sfnt';
					break;

				case 'woff':
					$header = 'Content-Type: application/font-woff';
					break;

				case 'eot':
					$header = 'Content-Type:  application/vnd.ms-fontobject';
					break;

				default:
					$header = false;
			}
			if ($header !== false)
			{
				if (!headers_sent())
				{
					header($header);
				}
				echo file_get_contents($path);
				return;
			}
		}

		if (!headers_sent())
		{
			header('HTTP/1.0 404 Not Found');
		}
	}

	/**
	 * @param int $currentPage
	 */
	public function index($currentPage = 1)
	{
		$this->loadMainData();

		$perPage = 50;
		$offset = ($currentPage > 1) ? (($currentPage - 1) * $perPage) : 0;

		$this->data['pageType'] = 'recent';
		$this->data['all_tweets'] = true;
		$filters = $this->data['filters'];
		$this->data['tweets'] =
			$this->model->getTweets($filters['own'], $filters['replies'], $filters['retweets'], $filters['favorites'], $offset, $perPage);
		$this->data['tweetsTotalCount'] = $this->data['totalTweets'];
		$this->data['pagination'] =
			$this->paginator->paginate($this->data['config']['system']['baseUrl'], $this->data['tweetsTotalCount'], $currentPage, $perPage);
		$this->data['title'] = $this->i18n->trans('all_tweets', ['ucf']);
		$this->data['subTitle'] = $offset ? ($this->i18n->trans('page', ['ucf']) . ' ' . $currentPage) : '';

		$this->render('index.php');
	}

	/**
	 * @param int $id
	 */
	public function tweet($id)
	{
		$this->loadMainData();

		$this->data['pageType'] = 'single';
		$this->data['single_tweet'] = true;
		$this->data['tweets'] = [$this->model->getTweet($id)];
		$this->data['title'] = '';
		$this->data['prevTweet'] = $this->model->getTweetBefore($id);
		$this->data['nextTweet'] = $this->model->getTweetAfter($id);
		$this->data['pagination'] = '';
		$this->data['title'] = $this->i18n->trans('individual_tweet', ['ucf']);

		$this->render('index.php');
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @param int $currentPage
	 */
	public function archive($year, $month, $day, $currentPage = 1)
	{
		$this->loadMainData();

		$perPage = 50;
		$offset = ($currentPage > 1) ? (($currentPage - 1) * $perPage) : 0;

		$this->data['pageType'] = 'archive';
		$this->data['monthly_archive'] = true;
		$this->data['archive_year'] = $year;
		$this->data['archive_month'] = $month;
		$this->data['archive_day'] = $day;
		if ($day)
		{
			$this->data['tweets'] = $this->model->getTweetsByDay($year, $month, $day, $offset, $perPage);
			$this->data['tweetsTotalCount'] = $this->model->getTweetsByDayCount($year, $month, $day);
			$pageBaseUrl = $this->data['config']['system']['baseUrl'] . 'archive/' . $year . '/' . $month . '/' . $day . '/';
			$date = str_pad($day, 2, '0', STR_PAD_LEFT) . '/' . str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year;
			$this->data['title'] = $this->i18n->trans('archives_from_day', ['ucf'], ['date' => $date]);
		}
		elseif ($month)
		{
			$this->data['tweets'] = $this->model->getTweetsByMonth($year, $month, $offset, $perPage);
			$this->data['tweetsTotalCount'] = $this->model->getTweetsByMonthCount($year, $month);
			$pageBaseUrl = $this->data['config']['system']['baseUrl'] . 'archive/' . $year . '/' . $month . '/';
			$this->data['title'] = $this->i18n->trans('archives_from_month', ['ucf'], ['date' => str_pad($month, 2, '0', STR_PAD_LEFT) . '/' . $year]);
		}
		else
		{
			$this->data['tweets'] = $this->model->getTweetsByYear($year, $offset, $perPage);
			$this->data['tweetsTotalCount'] = $this->model->getTweetsByYearCount($year);
			$pageBaseUrl = $this->data['config']['system']['baseUrl'] . 'archive/' . $year . '/';
			$this->data['title'] = $this->i18n->trans('archives_from_year', ['ucf'], ['date' => $year]);
		}
		$this->data['pagination'] = $this->paginator->paginate($pageBaseUrl, $this->data['tweetsTotalCount'], $currentPage, $perPage);

		$this->render('index.php');
	}

	/**
	 * @param int $currentPage
	 */
	public function favorites($currentPage = 1)
	{
		$this->loadMainData();

		$perPage = 50;
		$offset = ($currentPage > 1) ? (($currentPage - 1) * $perPage) : 0;

		$this->data['pageType'] = 'favorites';
		$this->data['favorite_tweets'] = true;
		$this->data['client'] = $_GET['favorites'];
		$this->data['tweets'] = $this->model->getFavoriteTweets($offset, $perPage);
		$this->data['tweetsTotalCount'] = $this->data['totalFavoriteTweets'];
		$pageBaseUrl = $this->data['config']['system']['baseUrl'] . 'favorites/';
		$this->data['pagination'] = $this->paginator->paginate($pageBaseUrl, $this->data['tweetsTotalCount'], $currentPage, $perPage);
		$this->data['title'] = $this->i18n->trans('favorite_tweets', ['ucf']);

		$this->render('index.php');
	}

	/**
	 * @param int $currentPage
	 */
	public function search($currentPage = 1)
	{
		$this->loadMainData();

		$perPage = 50;
		$offset = ($currentPage > 1) ? (($currentPage - 1) * $perPage) : 0;

		$searchTerm = str_replace('&quot;', '"', htmlspecialchars($_GET['q']));
		$this->data['pageType'] = 'search';
		$this->data['search'] = true;
		$this->data['searchTerm'] = $searchTerm;
		$this->data['tweets'] = $this->model->getSearchResults($searchTerm, $offset, $perPage);
		$this->data['tweetsTotalCount'] = $this->model->getSearchResults($searchTerm, $offset, $perPage, true);
		$pageBaseUrl = $this->data['config']['system']['baseUrl'] . '?q=' . urlencode($searchTerm);
		$this->data['pagination'] = $this->paginator->paginate($pageBaseUrl, $this->data['tweetsTotalCount'], $currentPage, $perPage, false);
		$this->data['title'] = $this->i18n->trans('search_results', ['ucf']);
		$this->data['subTitle'] = $searchTerm;

		$this->render('index.php');
	}

	protected function loadMainData()
	{
		// Get data for all index views.
		$this->data['twitterMonths'] = $this->model->getTwitterMonths();
		$this->data['maxTweets'] = $this->model->getMostTweetsInAMonth();
		$this->data['totalTweets'] = $this->model->getTotalTweets();
		$this->data['totalFavoriteTweets'] = $this->model->getTotalFavoriteTweets();
		$this->data['totalClients'] = $this->model->getTotalClients();
		$this->data['maxClients'] = $this->model->getMostPopularClientTotal();
		$this->data['title'] = '';
		$this->data['subTitle'] = '';
		$this->data['prevTweet'] = null;
		$this->data['nextTweet'] = null;

		// Filters.
		if (isset($_GET['f']) && is_array($_GET['f']))
		{
			$filters = ['own' => false, 'replies' => false, 'retweets' => false, 'favorites' => false];
			foreach ($_GET['f'] as $key => $value)
			{
				$filters[$key] = (bool)$value;
			}
		}
		else
		{
			$filters = ['own' => true, 'replies' => true, 'retweets' => true, 'favorites' => true];
		}
		$this->data['filters'] = $filters;
	}

	/**
	 * @param string $template
	 * @throws \Exception
	 */
	protected function render($template)
	{
		$this->data['content'] = $this->view->render($template, $this->data);
		$this->view->render('_layout.php', $this->data, true);
	}

	public function stats()
	{
		$this->data['maxTweets'] = $this->model->getMostTweetsInAMonth();
		$this->data['totalTweets'] = $this->model->getTotalTweets();
		$this->data['totalFavoriteTweets'] = $this->model->getTotalFavoriteTweets();
		$this->data['totalClients'] = $this->model->getTotalClients();
		$this->data['maxClients'] = $this->model->getMostPopularClientTotal();

		$this->data['pageType'] = 'stats';
		$this->render('stats.php');
	}

	public function notFound()
	{
		if (!headers_sent())
		{
			header('HTTP/1.0 404 Not Found');
		}
		$this->data['pageType'] = 'not-found';
		$this->data['content'] = '<h1>' . $this->i18n->trans('page_not_found', ['ucf']) . '</h1>';
		$this->view->render('_layout.php', $this->data, true);
	}
}