<?php
namespace Darathor\Amt\Entities;

/**
 * @name \Darathor\Amt\Entities\Mention
 */
class Mention implements \Darathor\Amt\Entities\Entity
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
		return 'https://twitter.com/' . $this->data['screen_name'];
	}

	/**
	 * @return string
	 */
	public function getExpandedUrl()
	{
		return $this->getUrl();
	}

	/**
	 * @return string
	 */
	public function getToken()
	{
		return '@' . $this->data['screen_name'];
	}

	/**
	 * @return string
	 */
	public function getDisplayName()
	{
		return '@' . $this->data['name'];
	}

	/**
	 * @param \Darathor\Amt\View $view
	 * @return string
	 * @throws \Exception
	 */
	public function renderHtmlFragment(\Darathor\Amt\View $view)
	{
		return $view->render('components/entity-mention.php', ['entity' => $this], false);
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