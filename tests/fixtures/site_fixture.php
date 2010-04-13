<?php
/* Site Fixture generated on: 0000-04-13 23:04:53 : 1271192873 */
class SiteFixture extends CakeTestFixture {

	public $name = 'Site';

	public $fields = array(
		'id' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 36, 'key' => 'primary'),
		'name' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50),
		'domain' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 50),
		'language' => array('type' => 'string', 'null' => false, 'default' => NULL, 'length' => 3),
		'modified' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'created' => array('type' => 'datetime', 'null' => false, 'default' => NULL),
		'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1)),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'MyISAM')
	);

}