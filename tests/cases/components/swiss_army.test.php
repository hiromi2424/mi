<?php
App::import('Component', array('Session', 'Mi.SwissArmy'));

class SwissArmyComponentTestCase extends CakeTestCase {

	function startTest() {
		$this->SwissArmy = new SwissArmyComponent();
		$this->SwissArmy->Session = new SessionComponent();
		$Controller = new Controller();
		$this->SwissArmy->initialize($Controller);
	}

	function endTest() {
		unset($this->SwissArmy);
		ClassRegistry::flush();
	}

	function testLoadComponent() {
		$this->assertTrue(empty($this->SwissArmy->Controller->Cookie));
		$this->SwissArmy->loadComponent('Cookie');
		$this->assertTrue(is_object($this->SwissArmy->Controller->Cookie));
		$this->assertTrue(get_class($this->SwissArmy->Controller->Cookie), 'CookieComponent');
	}

	function testBack() {
	}

	function testHandlePostActions() {
	}

	function testAutoLanguage() {
	}

	function testAutoLayout() {
	}

	function testLookup() {
	}

	function testParseSearchFilter() {
	}

	function testSetDefaultPageTitle() {
	}

	function testSetFilterFlash() {
	}

	function testSetSelects() {
	}
}