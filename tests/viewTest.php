<?php
namespace Darathor\Amt;

class ViewTest extends \PHPUnit_Framework_TestCase
{
	protected $templateDirectory;

	public function setUp()
	{
		$this->templateDirectory = dirname(__FILE__) . '/views';
		require_once dirname(__FILE__) . '/../includes.php';
	}

	public function testRender()
	{
		$view = new View($this->templateDirectory);
		$renderedTemplate = $view->render('index.php', ['content' => 'content']);
		$this->assertEquals('content', $renderedTemplate);
	}

	/**
	 * Test bad directory exception
	 * @expectedException \Exception
	 */
	public function testDirectoryException()
	{
		new View($this->templateDirectory . '/not_a_real_directory');
	}

	/**
	 * Test bad template exception
	 * @expectedException \Exception
	 */
	public function testTemplateException()
	{
		$view = new View($this->templateDirectory);
		$view->render($this->templateDirectory . '/not_a_real_template.php');
	}
}
