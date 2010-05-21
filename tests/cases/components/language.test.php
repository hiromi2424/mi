<?php
App::import('component', 'mi.language');

class languagecomponentTestCase extends CakeTestCase {

	function startTest() {
		$this->language = new languagecomponent();
	}

	function endTest() {
		unset($this->language);
		ClassRegistry::flush();
	}

}