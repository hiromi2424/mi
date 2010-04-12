<?php
class MiTestModel {

	function returnMicrotime() {
		trigger_error(__FUNCTION__);
		return getMicrotime();
	}

	function returnEmptyArray() {
		trigger_error(__FUNCTION__);
		return '';
	}

	function returnEmptyString() {
		trigger_error(__FUNCTION__);
		return '';
	}

	function returnNull() {
		trigger_error(__FUNCTION__);
		return null;
	}

	function returnZero() {
		trigger_error(__FUNCTION__);
		return 0;
	}

	function returnZeroString() {
		trigger_error(__FUNCTION__);
		return '0';
	}
}

class MiCacheTestCase extends CakeTestCase {

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

	function startTest() {
		MiCache::clear();
	}

	function endTest() {
		MiCache::clear();
	}
}