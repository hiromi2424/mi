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
	function returnMicrotime() {
		trigger_error(__FUNCTION__);
		return getMicrotime();
	}

/**
 * returnEmptyArray method
 *
 * @return void
 * @access public
 */
	function returnEmptyArray() {
		trigger_error(__FUNCTION__);
		return array();
	}

/**
 * returnEmptyString method
 *
 * @return void
 * @access public
 */
	function returnEmptyString() {
		trigger_error(__FUNCTION__);
		return '';
	}

/**
 * returnNull method
 *
 * @return void
 * @access public
 */
	function returnNull() {
		trigger_error(__FUNCTION__);
		return null;
	}

/**
 * returnZero method
 *
 * @return void
 * @access public
 */
	function returnZero() {
		trigger_error(__FUNCTION__);
		return 0;
	}

/**
 * returnZeroString method
 *
 * @return void
 * @access public
 */
	function returnZeroString() {
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
 * testMicrotime method
 *
 * @return void
 * @access public
 */
	function testMicrotime() {
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
 * startTest method
 *
 * @return void
 * @access public
 */
	function startTest() {
		MiCache::clear();
	}

/**
 * endTest method
 *
 * @return void
 * @access public
 */
	function endTest() {
		MiCache::clear();
	}
}