<?php
namespace Darathor\Amt\Entities;

/**
 * @name \Darathor\Amt\Entities\Image
 */
class Image implements \Darathor\Amt\Entities\Entity
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
		return (string)$this->data['media_url'];
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
	public function getLocalUrl()
	{
		return '/media/images/' . $this->getRelativePath();
	}

	/**
	 * @return string
	 */
	protected function getRelativePath()
	{
		$source = $this->getUrl();
		$extension = array_pop(explode('.', $source));
		return substr($this->data['id_str'], 0, 2) . '/' . $this->data['id_str'] . '.' . $extension;
	}

	/**
	 * @param \Darathor\Amt\View $view
	 * @return string
	 * @throws \Exception
	 */
	public function renderHtmlFragment(\Darathor\Amt\View $view)
	{
		return $view->render('components/entity-image.php', ['entity' => $this], false);
	}

	/**
	 * @param boolean $replace If true, the media will be re-downloaded if it is already here.
	 */
	public function download($replace = false)
	{
		$destination = ROOT_DIR . '/www/media/images/' . $this->getRelativePath();;
		if (!$replace && file_exists($destination))
		{
			return;
		}

		$contents = file_get_contents($this->getUrl());
		if ($contents)
		{
			$directory = dirname($destination);
			if (!is_dir($directory))
			{
				mkdir($directory, 0777, true);
			}
			file_put_contents($destination, $contents);
		}
	}

	/**
	 * @return bool
	 */
	public function isVisual()
	{
		return true;
	}
}