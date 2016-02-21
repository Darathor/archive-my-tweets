<?php
namespace Darathor\Amt\Entities;

/**
 * @name \Darathor\Amt\Entities\Link
 */
class Link implements \Darathor\Amt\Entities\Entity
{
	/**
	 * @var array
	 */
	protected $data;

	/**
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return (string)$this->data['expanded_url'];
	}

	/**
	 * @return string
	 */
	public function getExpandedUrl()
	{
		return (string)$this->data['expanded_url'];
	}

	/**
	 * @return string
	 */
	public function getToken()
	{
		return (string)$this->data['url'];
	}

	/**
	 * @return string
	 */
	public function getText()
	{
		return $this->data['display_url'];
	}

	/**
	 * @param \Darathor\Amt\View $view
	 * @return string
	 * @throws \Exception
	 */
	public function renderHtmlFragment(\Darathor\Amt\View $view)
	{
		return $view->render('components/entity-link.php', ['entity' => $this], false);
	}

	/**
	 * @param boolean $replace If true, the media will be re-downloaded if it is already here.
	 */
	public function download($replace = false)
	{
		// Nothing to do...
	}

	/**
	 * @return bool
	 */
	public function isVisual()
	{
		return false;
	}
}