<?php
/**
 * MiCache test case
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
 * @subpackage    mi.tests.cases.vendors
 * @since         v 1.0 (12-Apr-2010)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Vendor', 'Mi.MiCache');

/**
 * MiTestModel class
 *
 * @uses
 * @package       mi
 * @subpackage    mi.tests.cases.vendors
 */
class MiTestModel {

/**
 * returnMicrotime method
 *
 * @return void
 * @access public
 */
	public function returnMicrotime() {
		trigger_error(__FUNCTION__);
		return getMicrotime();
	}

/**
 * returnEmptyArray method
 *
 * @return void
 * @access public
 */
	public function returnEmptyArray() {
		trigger_error(__FUNCTION__);
		return array();
	}

/**
 * returnEmptyString method
 *
 * @return void
 * @access public
 */
	public function returnEmptyString() {
		trigger_error(__FUNCTION__);
		return '';
	}

/**
 * returnNull method
 *
 * @return void
 * @access public
 */
	public function returnNull() {
		trigger_error(__FUNCTION__);
		return null;
	}

/**
 * returnZero method
 *
 * @return void
 * @access public
 */
	public function returnZero() {
		trigger_error(__FUNCTION__);
		return 0;
	}

/**
 * returnZeroString method
 *
 * @return void
 * @access public
 */
	public function returnZeroString() {
		trigger_error(__FUNCTION__);
		return '0';
	}
}

/**
 * MiCacheTestCase class
 *
 * @uses          CakeTestCase
 * @package       mi
 * @subpackage    mi.tests.cases.vendors
 */
class MiCacheTestCase extends CakeTestCase {

/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	public $fixtures = array(
		'plugin.mi.site',
		'plugin.mi_settings.setting'
	);

/**
 * testDataMicrotime method
 *
 * @return void
 * @access public
 */
	public function testDataMicrotime() {
		$this->expectError('returnMicrotime');
		$time = MiCache::data('MiTestModel', 'returnMicrotime');
		do {
			$this->expectError('returnMicrotime');
			$directTime = ClassRegistry::init('MiTestModel')->returnMicrotime();
		} while ($directTime === $time);

		$cachedTime = MiCache::data('MiTestModel', 'returnMicrotime');
		$this->assertIdentical($time, $cachedTime);
	}

/**
 * testDataEmptyArray method
 *
 * @return void
 * @access public
 */
	public function testDataEmptyArray() {
		$method = 'returnEmptyArray';
		$expected = array();

		$this->expectError($method);
		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);

		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);
	}

/**
 * testDataEmptyString method
 *
 * @return void
 * @access public
 */
	public function testDataEmptyString() {
		$method = 'returnEmptyString';
		$expected = '';

		$this->expectError($method);
		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);

		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);
	}

/**
 * testDataNull method
 *
 * @return void
 * @access public
 */
	public function testDataNull() {
		$method = 'returnNull';
		$expected = null;

		$this->expectError($method);
		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);

		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);
	}

/**
 * testDataZero method
 *
 * @return void
 * @access public
 */
	public function testDataZero() {
		$method = 'returnZero';
		$expected = 0;

		$this->expectError($method);
		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);

		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);
	}

/**
 * testDataZeroString method
 *
 * @return void
 * @access public
 */
	public function testDataZeroString() {
		$method = 'returnZeroString';
		$expected = '0';

		$this->expectError($method);
		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);

		$return = MiCache::data('MiTestModel', $method);
		$this->assertIdentical($expected, $return);
	}

/**
 * testDataCacheDisabled method
 *
 * @return void
 * @access public
 */
	public function testDataCacheDisabled() {
		$cacheDisabled = Configure::read('Cache.disable');
		Configure::write('Cache.disable', true);

		$this->expectError('returnMicrotime');
		$time = MiCache::data('MiTestModel', 'returnMicrotime');
		do {
			$this->expectError('returnMicrotime');
			$directTime = ClassRegistry::init('MiTestModel')->returnMicrotime();
		} while ($directTime === $time);

		$this->expectError('returnMicrotime');
		$notCachedTime = MiCache::data('MiTestModel', 'returnMicrotime');
		$this->assertNotIdentical($time, $notCachedTime);

		Configure::write('Cache.disable', $cacheDisabled);
	}

/**
 * testConfigureSettings method
 *
 * @return void
 * @access public
 */
	public function testConfigureSettings() {
		Configure::write('MiCacheTest.one.two', 2);

		$expected = 2;
		$return = MiCache::setting('MiCacheTest.one.two');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.one.two');
		$this->assertIdentical($return, $expected);

		$expected = array('one' => array('two' => 2));
		$return = MiCache::setting('MiCacheTest');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest');
		$this->assertIdentical($return, $expected);

		$expected = array('two' => 2);
		$return = MiCache::setting('MiCacheTest.one');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.one');
		$this->assertIdentical($return, $expected);

		$expected = null;
		$return = MiCache::setting('MiCacheTest.one.three');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.one.three');
		$this->assertIdentical($return, $expected);
	}

/**
 * testConfigure method
 *
 * Make sure the Configure class behaves as expected
 * Run the tests twice to ensure the first and cached results are the same
 *
 * @return void
 * @access public
 */
	public function testConfigure() {
		$negatives = array(
			'array' => array(),
			'false' => false,
			'null' => null,
			'string' => '',
			'zero' => 0,
			'zeroString' => '0',
		);
		Configure::write('MiCacheTest.negatives', $negatives);
		$positives = array(
			'one' => 1,
			'true' => true,
		);
		Configure::write('MiCacheTest.positives', $positives);

		$expected = $negatives;
		$return = Configure::read('MiCacheTest.negatives');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives');
		$this->assertIdentical($return, $expected);

		$expected = array();
		$return = Configure::read('MiCacheTest.negatives.array');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.array');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.array');
		$this->assertIdentical($return, $expected);

		$expected = false;
		$return = Configure::read('MiCacheTest.negatives.false');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.false');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.false');
		$this->assertIdentical($return, $expected);

		$expected = null;
		$return = Configure::read('MiCacheTest.negatives.null');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.null');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.null');
		$this->assertIdentical($return, $expected);

		$expected = '';
		$return = Configure::read('MiCacheTest.negatives.string');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.string');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.string');
		$this->assertIdentical($return, $expected);

		$expected = 0;
		$return = Configure::read('MiCacheTest.negatives.zero');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.zero');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.zero');
		$this->assertIdentical($return, $expected);

		$expected = '0';
		$return = Configure::read('MiCacheTest.negatives.zeroString');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.zeroString');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.zeroString');
		$this->assertIdentical($return, $expected);

		$expected = $positives;
		$return = Configure::read('MiCacheTest.positives');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.positives');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.positives');
		$this->assertIdentical($return, $expected);

		$expected = 1;
		$return = Configure::read('MiCacheTest.positives.one');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.positives.one');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.positives.one');
		$this->assertIdentical($return, $expected);

		$expected = true;
		$return = Configure::read('MiCacheTest.positives.true');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.positives.true');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.positives.true');
		$this->assertIdentical($return, $expected);

		$expected = null;
		$return = Configure::read('MiCacheTest.doesnt.exist');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.doesnt.exist');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.doesnt.exist');
		$this->assertIdentical($return, $expected);
	}

/**
 * testConfigureOverrides method
 *
 * Run the tests twice to ensure the first and cached results are the same
 *
 * @return void
 * @access public
 */
	public function testConfigureOverrides() {
		$negatives = array(
			'array' => array(1,2,3),
			'false' => true,
			'null' => 'you can\'t override with null',
			'string' => 'not empty',
			'zero' => 1,
			'zeroString' => '1',
		);
		Configure::write('MiCacheTest.negatives', $negatives);

		$negatives = array(
			'array' => array(),
			'false' => false,
			'null' => null,
			'string' => '',
			'zero' => 0,
			'zeroString' => '0',
		);
		$Setting = ClassRegistry::init('MiSettings.Setting');
		$Setting->store('MiCacheTest.negatives', $negatives);

		$stored = $Setting->find('list', array(
			'conditions' => array(
				'id LIKE' => 'MiCacheTest.%'
			)
		));
		$expected = array(
			'MiCacheTest.negatives.array' => '[]',
			'MiCacheTest.negatives.false' => '',
			'MiCacheTest.negatives.null' => null,
			'MiCacheTest.negatives.string' => '',
			'MiCacheTest.negatives.zero' => '0',
			'MiCacheTest.negatives.zeroString' => '0',
		);
		$this->assertIdentical($stored, $expected);

		$expected = $negatives;
		$return = MiCache::setting('MiCacheTest.negatives');
		$this->assertIdentical($return, $expected);

		$expected = array();
		$return = MiCache::setting('MiCacheTest.negatives.array');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.array');
		$this->assertIdentical($return, $expected);

		$expected = false;
		$return = MiCache::setting('MiCacheTest.negatives.false');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.false');
		$this->assertIdentical($return, $expected);

		$expected = 'you can\'t override with null';
		$return = MiCache::setting('MiCacheTest.negatives.null');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.null');
		$this->assertIdentical($return, $expected);

		$expected = '';
		$return = MiCache::setting('MiCacheTest.negatives.string');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.string');
		$this->assertIdentical($return, $expected);

		$expected = 0;
		$return = MiCache::setting('MiCacheTest.negatives.zero');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.zero');
		$this->assertIdentical($return, $expected);

		$expected = '0';
		$return = MiCache::setting('MiCacheTest.negatives.zeroString');
		$this->assertIdentical($return, $expected);
		$return = MiCache::setting('MiCacheTest.negatives.zeroString');
		$this->assertIdentical($return, $expected);

		$Setting->deleteAll(array('id LIKE' => 'MiCacheTest%'));
	}

/**
 * testMi method
 *
 * It doesn't really matter which method is called
 *
 * @return void
 * @access public
 */
	function testMi() {
		$path =TMP . 'mi_cache_test' . DS;
		$Folder = new Folder($path, true);
		new File($path . 'one' . DS . 'empty.php', true);
		new File($path . 'two' . DS . 'empty.php', true);
		new File($path . 'three' . DS . 'empty.php', true);

		$expected = array(
			$path . 'one' . DS . 'empty.php',
			$path . 'two' . DS . 'empty.php',
			$path . 'three' . DS . 'empty.php'
		);
		$return = MiCache::mi('files', $path);
		$this->assertIdentical($return, $expected);

		$Folder->delete();
		$return = MiCache::mi('files', $path);
		$this->assertIdentical($return, $expected);
	}

/**
 * Ensure starting from a clear cache state
 *
 * @return void
 * @access public
 */
	public function startTest() {
		$this->_Configure = $Configure = Configure::getInstance();
		$Configure = new Configure();
		MiCache::clear();
	}

/**
 * Clean up after yourself
 *
 * @return void
 * @access public
 */
	public function endTest() {
		$Configure = Configure::getInstance();
		$Configure = $this->_Configure;
		MiCache::clear();
	}
}