<?php
App::import('Component', array('Session', 'Mi.MiSession'));

class MiSessionComponentTestCase extends CakeTestCase {

/**
 * testSessionFlash method
 *
 * @access public
 * @return void
 */
	function testSessionFlash() {
		$this->assertNull($this->Session->read('Message'));

		$this->Session->setFlash('This is a test message 1');
		$this->assertEqual(
			end($this->Session->read('Message')),
			array(
				'message' => 'This is a test message 1',
				'element' => 'default',
				'params' => array(
				)
			)
		);

		$this->Session->setFlash('This is a test message 2', 'test', array('name' => 'Joel Moss'));
		$this->assertEqual(
			end($this->Session->read('Message')),
			array(
				'message' => 'This is a test message 2',
				'element' => 'test',
				'params' => array(
					'name' => 'Joel Moss'
				)
			)
		);

		$this->Session->setFlash('This is a test message 3', 'default', array(), 'myFlash');
		$this->assertEqual(
			end($this->Session->read('Message')),
			array(
				'message' => 'This is a test message 3',
				'element' => 'default',
				'params' => array()
			)
		);

		$this->Session->setFlash('This is a test message 4', 'non_existing_element');
		$this->assertEqual(
			end($this->Session->read('Message')),
			array(
				'message' => 'This is a test message 4',
				'element' => 'non_existing_element',
				'params' => array()
			)
		);

		$allMessages = $this->Session->read('Message');
		$this->assertEqual(count($allMessages), 4);

		$this->Session->setFlash('Duplicate Message');
		$this->Session->setFlash('Duplicate Message');
		$this->Session->setFlash('Duplicate Message');
		$this->Session->setFlash('Duplicate Message');
		$this->Session->setFlash('Duplicate Message');
		$allMessages = $this->Session->read('Message');
		$this->assertEqual(count($allMessages), 5);

		$this->Session->delete('Message');
	}

	function testRedirect() {
	}

	function testRender() {
	}

	function startTest() {
		$this->Session = new MiSessionComponent();
		$this->Session->Session = new SessionComponent();
	}

	function endTest() {
		unset($this->MiSession);
		ClassRegistry::flush();
	}
}