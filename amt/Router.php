<?php
namespace Darathor\Amt;

class Router
{
	protected $controller;

	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
	}

	/**
	 * Calls a specific controller method based on $_GET['method']
	 * @param string $pathInfo
	 */
	public function route($pathInfo)
	{
		// Theme resources.
		if (strpos($pathInfo, '/theme/') === 0)
		{
			$this->controller->resource(substr($pathInfo, 6));
		}
		// Index.
		elseif (!$pathInfo || $pathInfo === '/')
		{
			$this->controller->index();
		}
		elseif (preg_match('#^/page/([0-9]+)/?#', $pathInfo, $matches))
		{
			$this->controller->index((int)$matches[1]);
		}
		// Single tweet.
		elseif (preg_match('#^/([0-9]+)/?#', $pathInfo, $matches))
		{
			$this->controller->tweet((int)$matches[1]);
		}
		// Search.
		elseif (strpos($pathInfo, '/search') === 0)
		{
			if ($pathInfo === '/search' || $pathInfo === '/search/')
			{
				$this->controller->search();
			}
			elseif (preg_match('#^/search/([0-9]+)/?#', $pathInfo, $matches))
			{
				$this->controller->search((int)$matches[1]);
			}
			else
			{
				$this->controller->notFound();
			}
		}
		// Archives.
		elseif (strpos($pathInfo, '/archive/') === 0)
		{
			// By day.
			if (preg_match('#^/archive/([0-9]{4})/([0-9]{2})/([0-9]{2})/?#', $pathInfo, $matches))
			{
				$this->controller->archive((int)$matches[1], (int)$matches[2], (int)$matches[3]);
			}
			elseif (preg_match('#^/archive/([0-9]{4})/([0-9]{2})/([0-9]{2})/page/([0-9]+)/?#', $pathInfo, $matches))
			{
				$this->controller->archive((int)$matches[1], (int)$matches[2], (int)$matches[3], (int)$matches[4]);
			}
			// By month.
			elseif (preg_match('#^/archive/([0-9]{4})/([0-9]{2})/?#', $pathInfo, $matches))
			{
				$this->controller->archive((int)$matches[1], (int)$matches[2], null);
			}
			elseif (preg_match('#^/archive/([0-9]{4})/([0-9]{2})/page/([0-9]+)/?#', $pathInfo, $matches))
			{
				$this->controller->archive((int)$matches[1], (int)$matches[2], null, (int)$matches[3]);
			}
			// By year.
			elseif (preg_match('#^/archive/([0-9]{4})/?#', $pathInfo, $matches))
			{
				$this->controller->archive((int)$matches[1], null, null);
			}
			elseif (preg_match('#^/archive/([0-9]{4})/page/([0-9]+)/?#', $pathInfo, $matches))
			{
				$this->controller->archive((int)$matches[1], null, null, (int)$matches[2]);
			}
			else
			{
				$this->controller->notFound();
			}
		}
		// Favorites.
		elseif (strpos($pathInfo, '/favorites/') === 0)
		{
			if (preg_match('#^/favorites/?#', $pathInfo, $matches))
			{
				$this->controller->favorites(0);
			}
			elseif (preg_match('#^/favorites/page/([0-9]+)/?#', $pathInfo, $matches))
			{
				$this->controller->favorites((int)$matches[1]);
			}
			else
			{
				$this->controller->notFound();
			}
		}
		// Not found.
		else
		{
			$this->controller->notFound();
		}
	}
}