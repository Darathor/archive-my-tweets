<?php
namespace Darathor\Core;

require_once ROOT_DIR . '/vendor/proximis/Change/Stdlib/FloatUtils.php';
require_once ROOT_DIR . '/vendor/proximis/Change/Stdlib/StringUtils.php';

require_once ROOT_DIR . '/core/Configuration.php';
require_once ROOT_DIR . '/core/I18n.php';

/**
 * @name \Darathor\Core\Core
 */
class Core
{
	/**
	 * @var \Darathor\Core\Configuration
	 */
	protected $configuration;

	/**
	 * @var \Darathor\Core\I18n
	 */
	protected $i18n;

	/**
	 * @param array $config
	 */
	public function __construct($config)
	{
		$this->configuration = new \Darathor\Core\Configuration($config);
	}

	/**
	 * @return \Darathor\Core\I18n
	 */
	public function getI18n()
	{
		if ($this->i18n === null)
		{
			$this->i18n = new \Darathor\Core\I18n($this->getConfiguration()->get('LCID'), $this->getConfiguration()->get('timezone'));
		}
		return $this->i18n;
	}

	/**
	 * @return \Darathor\Core\Configuration
	 */
	public function getConfiguration()
	{
		return $this->configuration;
	}
}