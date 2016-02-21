<?php
namespace Darathor\Amt;

class Paginator
{
	/**
	 * @var \Darathor\Amt\View
	 */
	protected $view;

	/**
	 * @param \Darathor\Amt\View $view
	 */
	public function __construct(\Darathor\Amt\View $view)
	{
		$this->view = $view;
	}

	/**
	 * Returns the HTML to display pagination links.
	 *
	 * @param string $baseUrl The URL template for links with a page number.
	 * @param int $total The total number of items across all pages.
	 * @param int $currentPage The current page to be displayed.
	 * @param int $perPage The total tweets per page.
	 * @param bool $niceUrl
	 * @return string The pagination links HTML.
	 */
	public function paginate($baseUrl, $total, $currentPage = 1, $perPage = 100, $niceUrl = true)
	{
		if ($total == 0)
		{
			return '';
		}

		$data = [
			'totalItems' => $total,
			'itemsPerPage' => $perPage,
			'totalPages' => ceil($total / $perPage),
			'currentPage' => $currentPage,
			'pageMarker' => ($niceUrl) ? 'page/' : '&page=',
			'baseUrl' => $baseUrl
		];

		return $this->view->render('components/pagination.php', $data, false);
	}
}