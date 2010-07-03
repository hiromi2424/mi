<?php
/**
 * Test case for swiss army component
 *
 * Currently only covers a fraction of the codebase
 *
 * PHP version 5
 *
 * Copyright (c) 2010, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright     Copyright (c) 2010, Andy Dawson
 * @link          www.ad7six.com
 * @package       mi
 * @subpackage    mi.tests.cases.components
 * @since         v 1.0 (29-Jun-2010)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Component', array('Session', 'Mi.SwissArmy'));

/**
 * TestSwissArmyController class
 *
 * Test controller that doesn't do anything, just so we can use testAction and get semi realistic
 * results
 *
 * @uses          Controller
 * @package       mi
 * @subpackage    mi.tests.cases.components
 */
class TestSwissArmyController extends Controller {

/**
 * here property
 *
 * @var string '/'
 * @access public
 */
	public $here = '/';

/**
 * referer property
 *
 * @var string '/'
 * @access public
 */
	public $referer = '/';

/**
 * uses property
 *
 * @var array
 * @access public
 */
	public $uses = array();

/**
 * actions property
 *
 * @var array
 * @access public
 */
	public $actions = array();

/**
 * components property
 *
 * @var array
 * @access public
 */
	public $components = array(
		'Mi.TestSwissArmy'
	);

/**
 * Fictional ids of data that exists in the DB
 *
 * @var array
 * @access public
 */
	public $ids = array(
		1 => 1, 2, 3, 4, 5
	);

/**
 * add method
 *
 * @return void
 * @access public
 */
	function add() {
		$this->render('add');
	}

/**
 * edit method
 *
 * @param mixed $id null
 * @return void
 * @access public
 */
	function edit($id = null) {
		if (!isset($this->ids[$id]) || $this->data) {
			return $this->_back();
		}
		$this->render('edit:' . $id);
	}

/**
 * delete method
 *
 * @param mixed $id null
 * @return void
 * @access public
 */
	function delete($id = null) {
		unset ($this->ids[$id]);
		$this->actions[] = 'delete';
		$this->_back();
	}

/**
 * view method
 *
 * @param mixed $id null
 * @return void
 * @access public
 */
	function view($id = null) {
		if (!isset($this->ids[$id])) {
			return $this->_back();
		}
		$this->render('view:' . $id);
	}

/**
 * index method
 *
 * @param int $page 1
 * @return void
 * @access public
 */
	function index($page = 1) {
		return $this->render('index:' . $page);
	}

/**
 * here method
 *
 * @return void
 * @access public
 */
	function here() {
		return end($this->actions);
	}

/**
 * referer method
 *
 * @return void
 * @access public
 */
	function referer() {
		$return = Configure::read('Testing.referer');
		if ($return) {
			return $return;
		}
		return $this->referer;
	}

/**
 * render method
 *
 * @param mixed $action null
 * @return void
 * @access public
 */
	function render($action = null) {
		$this->actions[] = $action;
		$this->TestSwissArmy->beforeRender();
		return 'rendered ' . $action;
	}

/**
 * back method
 *
 * @return void
 * @access protected
 */
	function _back() {
		$url = $this->TestSwissArmy->back(1, null, false);
		$this->actions[] = $url;
		return 'redirect to ' . $url;
	}
}
/**
 * TestSwissArmyComponent class
 *
 * @uses          SwissArmyComponent
 * @package       mi
 * @subpackage    mi.tests.cases.components
 */
class TestSwissArmyComponent extends SwissArmyComponent {

/**
 * webroot property
 *
 * @var string '/'
 * @access public
 */
	public $webroot = '/';

/**
 * addToHistory method
 *
 * manually append to the history stack
 *
 * @param mixed $url
 * @param mixed $referer null
 * @return void
 * @access public
 */
	function addToHistory($url, $referer = null) {
		$thread = $this->_browseKey();
		if (!$referer) {
			if ($this->_history) {
				end($this->_history[$thread]);
				$referer = key($this->_history[$thread]);
				if ($referer === $url && !empty($this->_history[$thread][$url])) {
					return;
				}
			} else {
				$referer = '/';
			}
		}
		$this->_history[$thread][$url] = $referer;
	}

/**
 * history method
 *
 * wrapper for debugging purposes
 *
 * @return void
 * @access public
 */
	function history() {
		return $this->_history;
	}

/**
 * initialize method
 *
 * If we're testing ensure the here values are correct
 *
 * @param mixed $C
 * @param array $config array()
 * @return void
 * @access public
 */
	function initialize(&$C, $config = array()) {
		$currentUrl = Configure::read('Testing.url');
		if ($currentUrl) {
			$C->here = $currentUrl;
			$this->_here = $currentUrl;
		}
		return parent::initialize($C, $config = array());
	}

/**
 * simulate method
 *
 * Simulate here and referer info to bypass controller testing
 *
 * @param mixed $here
 * @param mixed $referer null
 * @param array $history array()
 * @return void
 * @access public
 */
	function simulate($here, $referer = null,  $history = array()) {
		$this->_here = $here;
		$this->_referer = $referer;

		$thread = $this->_browseKey();
		$this->_history[$thread] = $history;

		if (isset($this->_history[$thread][$this->_here])) {
			$this->_last = $this->_history[$thread][$this->_here];
		} elseif ($this->_referer !== '/') {
			$this->_last = $this->_referer;
		} else {
			$this->_last = $this->_fallBack;
		}
	}

/**
 * normalizeUrl method
 *
 * Ensure the webroot and here are set correctly
 *
 * @param mixed $url
 * @param bool $key false
 * @return void
 * @access protected
 */
	protected function _normalizeUrl($url, $key = false) {
		if (!$this->_here) {
			$this->_here = '/';
		}
		$this->webroot = '/';
		return parent::_normalizeUrl($url, $key);
	}
}

/**
 * SwissArmyComponentTestCase class
 *
 * @uses          CakeTestCase
 * @package       mi
 * @subpackage    mi.tests.cases.components
 */
class SwissArmyComponentTestCase extends CakeTestCase {

/**
 * startTest method
 *
 * @return void
 * @access public
 */
	function startTest() {
		Router::reload();
		$this->SwissArmy = new TestSwissArmyComponent();
		$this->SwissArmy->Session = new SessionComponent();
		$this->Controller = new TestSwissArmyController();
		$this->Controller->TestSwissArmy = $this->SwissArmy;
		$this->SwissArmy->initialize($this->Controller);
	}

/**
 * endTest method
 *
 * @return void
 * @access public
 */
	function endTest() {
		unset($this->SwissArmy);
		ClassRegistry::flush();
	}

/**
 * testLoadComponent method
 *
 * @return void
 * @access public
 */
	function testLoadComponent() {
		$this->assertTrue(empty($this->SwissArmy->Controller->Cookie));
		$this->SwissArmy->loadComponent('Cookie');
		$this->assertTrue(is_object($this->SwissArmy->Controller->Cookie));
		$this->assertTrue(get_class($this->SwissArmy->Controller->Cookie), 'CookieComponent');
	}

/**
 * testBackNormal method
 *
 * With simulated normal use - check the back function goes where it's supposed to
 *
 * @return void
 * @access public
 */
	function testBackNormal() {
		$history = array(
			'/' => '/news',
			'/news' => '/news/view/1',
			'/comments/view/news/1' => '/news/view/1',
			'/comments/add/news/1' => '/comments/view/news/1', // simulate a user opening a new tab to comment
			'/comments/view/news/1/page:2' => '/comments/view/news/1',
			'/profiles/bobby_drop_tables' => '/comments/view/news/1/page:2',
			'/comments/view/69/what_an_interesting_article' => '/comments/add/news/1', // tab #2
			'/comments/add/profiles/bobby_drop_tables' => '/profiles/bobby_drop_tables'
		);

		$this->SwissArmy->simulate('/comments/add/profiles/bobby_drop_tables', '/profiles/bobby_drop_tables', $history);
		$result = $this->SwissArmy->back(1, null, false);
		$expected = '/profiles/bobby_drop_tables';
		$this->assertEqual($result, $expected);

		// User has a validation error, and therefore loses the http referer
		$this->SwissArmy->simulate('/comments/add/profiles/bobby_drop_tables', '/comments/add/profiles/bobby_drop_tables', $history);
		$result = $this->SwissArmy->back(1, null, false);
		$this->assertEqual($result, $expected);
	}

/**
 * testBackLoginNormal method
 *
 * If you arrive at a login form - do you get sent back to where you came from
 *
 * @return void
 * @access public
 */
	function testBackLoginNormal() {
		$history = array(
			'/' => '/news',
			'/news' => '/news/view/1',
			'/comments/view/news/1' => '/news/view/1',
			'/users/login' => '/comments/view/news/1'
		);

		$this->SwissArmy->simulate('/users/login', '/comments/view/news/1', $history);
		$result = $this->SwissArmy->back(1, null, false);
		$expected = '/comments/view/news/1';
		$this->assertEqual($result, $expected);

		// User has a validation error, and therefore loses the http referer
		$this->SwissArmy->simulate('/users/login', '/users/login', $history);
		$result = $this->SwissArmy->back(1, null, false);
		$this->assertEqual($result, $expected);
	}

/**
 * testBackLoginNoRender method
 *
 * If there is a login form embedded in the layout - i.e. doesn't render a page of its own - do you still get
 * sent correctly to where you were (i.e. you don't move, it just post submits to /users/login and then reloads
 * the page you were on)
 *
 * @return void
 * @access public
 */
	function testBackLoginNoRender() {
		$history = array(
			'/' => '/news',
			'/news' => '/news/view/1',
			'/comments/view/news/1' => '/news/view/1',
		);

		$this->SwissArmy->simulate('/users/login', '/comments/view/news/1', $history);
		$result = $this->SwissArmy->back(1, null, false);
		$expected = '/comments/view/news/1';
		$this->assertEqual($result, $expected);
	}

/**
 * testBackWithController method
 *
 * A more thorough test which currently doesn't work
 *
 * @return void
 * @access public
 */
	function testBackWithController() {
		return;
		$params = array('method' => 'get', 'return' => 'view'); // not really, render is overriden

		$this->testAction('/test_swiss_army/index', $params);

		$this->testAction('/test_swiss_army/index/2', $params);

		$this->testAction('/test_swiss_army/index/3', $params);

		$this->testAction('/test_swiss_army/view/5', $params);
		$this->SwissArmy->initializeBackData();
		$referer = $this->SwissArmy->back(1, null, false);
		$this->assertEqual($referer, '/test_swiss_army/index/3');

		$this->testAction('/test_swiss_army/edit/5', $params);
		$params['method'] = 'post';
		$params['data'] = array('notmuch');
		$this->testAction('/test_swiss_army/edit/5', $params);
		$this->SwissArmy->initializeBackData();
		$referer = $this->SwissArmy->back(1, null, false);
		$this->assertEqual($referer, '/test_swiss_army/view/5');
	}

/**
 * testHandlePostActions method
 *
 * @return void
 * @access public
 */
	function testHandlePostActions() {
	}

/**
 * testAutoLanguage method
 *
 * @return void
 * @access public
 */
	function testAutoLanguage() {
	}

/**
 * testAutoLayout method
 *
 * @return void
 * @access public
 */
	function testAutoLayout() {
	}

/**
 * testLookup method
 *
 * @return void
 * @access public
 */
	function testLookup() {
	}

/**
 * testParseSearchFilter method
 *
 * @return void
 * @access public
 */
	function testParseSearchFilter() {
	}

/**
 * testSetDefaultPageTitle method
 *
 * @return void
 * @access public
 */
	function testSetDefaultPageTitle() {
	}

/**
 * testSetFilterFlash method
 *
 * @return void
 * @access public
 */
	function testSetFilterFlash() {
	}

/**
 * testSetSelects method
 *
 * @return void
 * @access public
 */
	function testSetSelects() {
	}

/**
 * testAction method
 *
 * Automatically set request info
 *
 * @param mixed $url
 * @param array $params array()
 * @return void
 * @access public
 */
	function testAction($url, $params = array()) {
		static $referer = null;

		Configure::write('Testing.url', $url);
		Configure::write('Testing.referer', $referer);
		Configure::write('App.base', '/');
		Configure::write('App.baseUrl', '/');
		Router::setRequestInfo(array(
			array(
				'controller' => 'test_swiss_army',
				'action' => 'never used',
				'url' => array(
					'url' => $url
				)
			),
			array(
				'base' => '/',
				'here' => $url,
				'webroot' => '/',
				'passedArgs' => array(),
				'namedArgs' => array(),
			)
		));
		$return = parent::testAction($url, $params);
		$referer = $url;
		return $return;
	}
}