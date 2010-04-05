<?php
/**
 * OneQuery Test cases
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
 * @subpackage    mi.tests.cases.behaviors
 * @since         v 1.0 (05-Apr-2010)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Core', array('AppModel', 'Model'));
require_once(CAKE_TESTS . 'cases' . DS . 'libs' . DS . 'model' . DS . 'models.php');
App::import('Behavior', 'OneQuery');

/**
 * OneQueryBehaviorTestCase class
 *
 * Structurally based on the containable test case
 *
 * @uses          CakeTestCase
 * @package       mi
 * @subpackage    mi.tests.cases.behaviors
 */
class OneQueryBehaviorTestCase extends CakeTestCase {

/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	public $fixtures = array(
		'core.article',
		'core.article_featured',
		'core.article_featureds_tags',
		'core.articles_tag',
		'core.attachment',
		'core.category',
		'core.comment',
		'core.featured',
		'core.tag',
		'core.user'
	);

/**
 * testControl method
 *
 * Run some simple queries and check the query methods work as expected
 *
 * @return void
 * @access public
 */
	function testControl() {
		$this->Article->recursive = -1;
		$this->Article->find('all');
		$this->Article->find('list');
		$this->Article->find('count');

		$lastQuery = $this->_lastQuery();
		$this->assertQuery($lastQuery, 'SELECT COUNT(*) AS count FROM articles AS Article');

		$queries = $this->_queries();
		$this->assertEqual(count($queries), 3);

		$queryCount = $this->_queryCount();
		$this->assertEqual($queryCount, 3);
	}

/**
 * testOneQueryExplicit method
 *
 * @return void
 * @access public
 */
	function testOneQueryExplicit() {
		$this->Article->oneQuery(array('Comment'));
		$after = $this->_associations($this->Article);

		$this->assertAssociations($this->Article, array(
			'belongsTo' => array(
				'User',
				'Comment' => array(
					'foreignKey' => false,
					'conditions' => array(
						'Comment.article_id = Article.id'
					)
				)
			)
		));
	}

/**
 * Method executed before each test
 *
 * Setup models, clear the dbo sql log
 *
 * @return void
 * @access public
 */
	function startTest() {
		$this->User =& ClassRegistry::init('User');
		$this->Article =& ClassRegistry::init('Article');
		$this->Tag =& ClassRegistry::init('Tag');

		$this->db =& ConnectionManager::getDataSource('test_suite');

		$this->User->bindModel(array(
			'hasMany' => array(
				'Article',
				'ArticleFeatured',
				'Comment'
			)
		), false);

		$this->User->ArticleFeatured->unbindModel(array(
			'belongsTo' => array('Category')
		), false);

		$this->User->ArticleFeatured->hasMany['Comment']['foreignKey'] = 'article_id';

		$this->Tag->bindModel(array(
			'hasAndBelongsToMany' => array('Article')
		), false);

		$this->User->Behaviors->attach('Mi.OneQuery');
		$this->Article->Behaviors->attach('Mi.OneQuery');
		$this->Tag->Behaviors->attach('Mi.OneQuery');

		$this->db->fullDebug = true;
		$this->db->_queriesCnt = 0;
		$this->db->_queriesTime = null;
		$this->db->_queriesLog = array();
		$this->db->_queriesLogMax = 200;
	}

/**
 * Method executed after each test
 *
 * @return void
 * @access public
 */
	function endTest() {
		unset($this->Article);
		unset($this->User);
		unset($this->Tag);

		ClassRegistry::flush();
	}

/**
 * Check associations are setup as expected
 *
 * @param mixed $Model
 * @param array $expected array()
 * @return void
 * @access public
 */
	function assertAssociations(&$Model, $expected = array()) {
		$associationTypes = array_keys($expected);
		$associations = $this->_associations($Model, $associationTypes);

		$this->assertEqual($associations, $expected);
	}

/**
 * assertQuery method
 *
 * @param mixed $query
 * @param array $expected array()
 * @return void
 * @access public
 */
	function assertQuery($query, $expected = array()) {
		$query = $this->_normalizeQuery($query);
		$expected = $this->_normalizeQuery($expected);
		$this->assertEqual($query, $expected);
	}

/**
 * associations method
 *
 * Get all assoociations, taking account of most defaults to make comparisions more concise
 * $associationTypes can be true, for all. null for just belongsTo, hasOne, or an array
 * defaults to just belongsTo and hasOne
 *
 * @param mixed $Model
 * @param mixed $associationTypes null
 * @return string
 * @access protected
 */
	function _associations(&$Model, $associationTypes = null) {
		if ($associationTypes === true) {
			$associationTypes = array('belongsTo', 'hasOne', 'hasMany', 'hasAndBelongsToMany');
		} elseif ($associationTypes === null) {
			$associationTypes = array('belongsTo', 'hasOne');
		}

		$ignoreKeys = array('dependent', 'unique');

		$return = array();
		foreach($associationTypes as $association) {
			$return[$association] = array();
			if (!empty($Model->$association)) {
				foreach($Model->$association as $alias => $row) {
					if (!is_numeric($alias)) {
						foreach($ignoreKeys as $key) {
							unset($row[$key]);
						}
						if ($association === 'belongsTo') {
							if (!empty($row['foreignKey']) && $row['foreignKey'] === Inflector::underscore($alias) . '_id') {
								unset($row['foreignKey']);
							}
						} else {
							if (!empty($row['foreignKey']) && $row['foreignKey'] === Inflector::underscore($Model->alias) . '_id') {
								unset($row['foreignKey']);
							}
							if (!empty($row['associationForeignKey']) && $row['associationForeignKey'] === Inflector::underscore($alias) . '_id') {
								unset($row['associationForeignKey']);
							}
							if (!empty($row['joinTable'])) {
								$tables = array($Model->table, Inflector::pluralize(Inflector::underscore($alias)));
								sort ($tables);
								if ($row['joinTable'] === ($tables[0] . '_' . $tables[1])) {
									unset($row['joinTable']);
								}
							}
							if (!empty($row['with'])) {
								$aliases = array(Inflector::classify($Model->alias), $alias);
								sort ($aliases);
								if ($row['with'] === (Inflector::pluralize($aliases[0]) . $aliases[1])) {
									unset($row['with']);
								}
							}
						}
						if ($row['className'] === $alias) {
							unset($row['className']);
						}
						if (!empty($row['conditions'])) {
							$row['conditions'] = sort($row['conditions']);
						}

						foreach($row as $key => $value) {
							if (!$value && $value !== false) {
								unset ($row[$key]);
							}
						}
						if (!$row) {
							$row = $alias;
							$alias = count($return[$association]);
						}
					}
					$return[$association][$alias] = $row;
				}
			}
		}
		return $return;
	}

/**
 * return the last executed query
 *
 * @return string
 * @access protected
 */
	function _lastQuery() {
		$this->db =& ConnectionManager::getDataSource($this->Article->useDbConfig);
		return current(end($this->db->_queriesLog));
	}

/**
 * normalizeQuery method
 *
 * Remove table prefixes, db-quotes and other needless bits of queriesi
 * to make things easier to compare
 *
 * @param mixed $query
 * @return void
 * @access protected
 */
	function _normalizeQuery($query) {
		$query = str_replace(array(
			$this->db->startQuote,
			$this->db->endQuote,
			$this->db->config['prefix'],
			'WHERE 1 = 1',
		), '', $query);
		return trim(preg_replace('@\s+@', ' ', $query));
	}

/**
 * return all queries
 *
 * @return array
 * @access protected
 */
	function _queries() {
		if ($this->db->_queriesCnt === $this->db->_queriesLogMax) {
			trigger_error('OneQueryTest::_queryCount The max log size has been reached results may not be accurate');
		}

		$return = array();
		foreach($this->db->_queriesLog as $row) {
			if (strpos($row['query'], 'DESCRIBE') === 0) {
				continue;
			}
			$return[] = $row['query'];
		}
		return $return;
	}

/**
 * return number of queries executed
 *
 * @return int
 * @access protected
 */
	function _queryCount() {
		return count($this->_queries());
	}
}