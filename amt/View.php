<?php
namespace Darathor\Amt;

class View
{
	/**
	 * @var \Darathor\Core\I18n|null
	 */
	protected $i18n;

	/**
	 * @var \Darathor\Core\Configuration|null
	 */
	protected $configuration;

	/**
	 * @var string
	 */
	protected $templateDirectory;

	/**
	 * @param string $dir
	 * @param \Darathor\Core\I18n $i18n
	 * @param \Darathor\Core\Configuration $configuration
	 * @throws \Exception
	 */
	public function __construct($dir, \Darathor\Core\I18n $i18n = null, \Darathor\Core\Configuration $configuration = null)
	{
		$this->setTemplateDirectory($dir);
		$this->i18n = $i18n;
		$this->configuration = $configuration;
	}

	/**
	 * Sets the template directory
	 *
	 * @param string $dir
	 * @return bool Returns TRUE on success, FALSE if the directory is invalid.
	 * @throws \Exception
	 */
	public function setTemplateDirectory($dir)
	{
		if (!is_dir($dir))
		{
			throw new \Exception('Template directory is invalid: ' . $dir);
		}
		$this->templateDirectory = rtrim($dir, '/');
		return true;
	}

	/**
	 * Render a template with optional data.
	 * @param string $template
	 * @param array $data
	 * @param bool $echoRenderedTemplate
	 * @return string
	 * @throws \Exception
	 */
	public function render($template, $data = [], $echoRenderedTemplate = false)
	{
		$templatePath = $this->templateDirectory . '/' . ltrim($template, '/');
		if (!file_exists($templatePath))
		{
			throw new \Exception('Template not found: ' . $templatePath);
		}

		$rendered = $this->_render($templatePath, $data);
		if ($echoRenderedTemplate)
		{
			echo $rendered;
		}
		return $rendered;
	}

	/**
	 * Returns a rendered template string.
	 * @param $templatePath
	 * @param $data
	 * @return string
	 */
	private function _render($templatePath, $data)
	{
		// extract data variables into the local scope
		extract($data);
		ob_start();
		require $templatePath;
		return ob_get_clean();
	}
}