<?php
App::import('View', 'Mi.Mi');

class MiTestView extends MiView {

/**
 * getViewFileName method
 *
 * @param mixed $name
 * @access public
 * @return void
 */
	function getViewFileName($name = null) {
		return $this->_getViewFileName($name);
	}

/**
 * getLayoutFileName method
 *
 * @param mixed $name
 * @access public
 * @return void
 */
	function getLayoutFileName($name = null) {
		return $this->_getLayoutFileName($name);
	}

/**
 * loadHelpers method
 *
 * @param mixed $loaded
 * @param mixed $helpers
 * @param mixed $parent
 * @access public
 * @return void
 */
	function loadHelpers(&$loaded, $helpers, $parent = null) {
		return $this->_loadHelpers($loaded, $helpers, $parent);
	}

/**
 * paths method
 *
 * @param string $plugin
 * @param boolean $cached
 * @access public
 * @return void
 */
	function paths($plugin = null, $cached = true) {
		return $this->_paths($plugin, $cached);
	}

/**
 * cakeError method
 *
 * @param mixed $method
 * @param mixed $messages
 * @access public
 * @return void
 */
	function cakeError($method, $messages) {
		return new ViewTestErrorHandler($method, $messages);
	}
}
/*
require_once TEST_CAKE_CORE_INCLUDE_PATH . 'tests' . DS . 'cases' . DS . 'libs' . DS . 'view' . DS . 'view.test.php';
/**
 * MiViewDoesntBreakCoreTest class
 *
 * @TODO At the time of writing the core test gives 6 fails and 6 exceptions which this test
 * inherits
 * @uses          ViewTest
 * @package       mi
 * @subpackage    mi.tests.cases.views
 * /
class MiViewDoesntBreakCoreTest extends ViewTest {

/**
 * setup method
 *
 * @return void
 * @access public
 * /
	function setup() {
		parent::setup();
		$this->View = new MiView($this->PostsController);
	}
}
/**/

class MiPostsController extends Controller {

	public $uses = array();

	public $helpers = array();
}

class MiViewTest extends CakeTestCase {

	function setup() {
		$this->PostsController = new MiPostsController();
		$this->View = new MiTestView($this->PostsController);
	}

	function testloadHelpers() {
		$loaded = array();
		$toLoad = array(
			'MiHtml',
			'MiForm',
			'Paginator' // Automatically added by cake
		);
		$helpers = $this->View->loadHelpers($loaded, $toLoad);

		$this->assertTrue(is_object($helpers['Html']));
		$this->assertTrue(get_class($helpers['Html']), 'MiHtmlHelper');

		$this->assertTrue(is_object($helpers['Form']));
		$this->assertTrue(get_class($helpers['Form']), 'MiHtmlHelper');

		$this->assertTrue(is_object($helpers['Paginator']));
		$this->assertTrue(get_class($helpers['Paginator']), 'MiPaginatorHelper');
	}

	function testPaths() {
	}

	function testRender() {
	}

	function testElement() {
	}

	function testEntity() {
	}
}