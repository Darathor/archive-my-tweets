<?php
namespace Darathor\Amt;

/**
 * @name \Darathor\Amt\Avatar
 */
class Avatar
{
	/**
	 * @var string|null
	 */
	protected $url;

	/**
	 * @var integer|null
	 */
	protected $id;

	/**
	 * @param $url
	 */
	public function setUrl($url)
	{
		$parts = explode('/', $url);
		if (count($parts) > 2)
		{
			$this->id = (int)$parts[count($parts) - 2];
			$this->url = $url;
		}
	}

	/**
	 * @param bool $checkFile
	 * @return bool
	 */
	public function isValid($checkFile = true)
	{
		return $this->url && $this->id && (!$checkFile || file_exists($this->getAbsolutePath()));
	}

	/**
	 * @return string|null
	 */
	protected function getRelativePath()
	{
		$extension = array_pop(explode('.', $this->url));
		return substr((string)$this->id, 0, 2) . '/' . (string)$this->id . '.' . $extension;
	}

	/**
	 * @return string|null
	 */
	protected function getAbsolutePath()
	{
		return ROOT_DIR . '/www/media/avatars/' . $this->getRelativePath();
	}

	/**
	 * @param boolean $replace If true, the media will be re-downloaded if it is already here.
	 */
	public function download($replace = false)
	{
		if (!$this->isValid(false))
		{
			return;
		}

		$destination = $this->getAbsolutePath();
		if (!$replace && file_exists($destination))
		{
			return;
		}

		$contents = file_get_contents($this->url);
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
	 * Returns the avatar URL.
	 * @return string|null
	 */
	public function getLocalUrl()
	{
		return $this->isValid() ? '/media/avatars/' . $this->getRelativePath() : null;
	}

	/**
	 * @param \Darathor\Amt\View $view
	 * @return string
	 * @throws \Exception
	 */
	public function renderHtmlFragment(\Darathor\Amt\View $view)
	{
		return $view->render('components/avatar.php', ['avatar' => $this], false);
	}
}