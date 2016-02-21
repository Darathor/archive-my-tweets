<?php
namespace Darathor\Amt\Entities;

/**
 * @name \Darathor\Amt\Entities\Video
 */
class Video implements \Darathor\Amt\Entities\Entity
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
	public function getThumbnailUrl()
	{
		return '/media/thumbnails/' . $this->getThumbnailRelativePath();
	}

	/**
	 * @return string
	 */
	protected function getThumbnailRelativePath()
	{
		$source = $this->getUrl();
		$extension = array_pop(explode('.', $source));
		return substr($this->data['id_str'], 0, 2) . '/' . $this->data['id_str'] . '.' . $extension;
	}

	/**
	 * @param \Darathor\Core\I18n $i18n
	 * @return string
	 */
	public function getText(\Darathor\Core\I18n $i18n)
	{
		if (isset($this->data['video_info']['duration_millis']))
		{
			$args = [
				'seconds' => (int)($this->data['video_info']['duration_millis'] / 1000),
				'minutes' => 0,
				'hours' => 0
			];

			if ($args['seconds'] > 60)
			{
				$args['minutes'] = (int)($args['seconds'] / 60);
				$args['seconds'] %= 60;
			}
			if ($args['minutes'] > 60)
			{
				$args['hours'] = (int)($args['minutes'] / 60);
				$args['minutes'] %= 60;
			}

			if ($args['hours'])
			{
				return $i18n->trans('duration', ['ucf', 'lab']) . ' ' . $i18n->trans('duration_hours', [], $args);
			}
			elseif ($args['minutes'])
			{
				return $i18n->trans('duration', ['ucf', 'lab']) . ' ' . $i18n->trans('duration_minutes', [], $args);
			}
			else
			{
				return $i18n->trans('duration', ['ucf', 'lab']) . ' ' . $i18n->trans('duration_seconds', [], $args);
			}
		}
		else
		{
			return null;
		}
	}

	/**
	 * @param \Darathor\Amt\View $view
	 * @return string
	 * @throws \Exception
	 */
	public function renderHtmlFragment(\Darathor\Amt\View $view)
	{
		return $view->render('components/entity-video.php', ['entity' => $this], false);
	}

	/**
	 * @param boolean $replace If true, the media will be re-downloaded if it is already here.
	 */
	public function download($replace = false)
	{
		$destination = ROOT_DIR . '/www/media/thumbnails/' . $this->getThumbnailRelativePath();;
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