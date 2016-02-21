<?php
namespace Darathor\Amt\Entities;

/**
 * @name \Darathor\Amt\Entities\Media
 */
interface Entity
{
	/**
	 * @return string
	 */
	public function getToken();

	/**
	 * @param \Darathor\Amt\View $view
	 * @return string
	 * @throws \Exception
	 */
	public function renderHtmlFragment(\Darathor\Amt\View $view);

	/**
	 * @param boolean $replace If true, the media will be re-downloaded if it is already here.
	 */
	public function download($replace = false);

	/**
	 * @returns boolean
	 */
	public function isVisual();
}