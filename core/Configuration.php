<?php
namespace Darathor\Core;

/**
 * @name \Darathor\Core\Configuration
 */
class Configuration
{
	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @param array $config
	 */
	public function __construct(array $config)
	{
		$this->config = $config;
	}

	/**
	 * @return array|mixed
	 */
	public function get()
	{
		$tmp = $this->config;
		foreach (func_get_args() as $arg)
		{
			if (isset($tmp[$arg]))
			{
				$tmp = $tmp[$arg];
			}
		}
		return $tmp;
	}
}