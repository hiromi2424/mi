<?php
/**
 * Helper for generating menus - usually <ul>s
 *
 * PHP version 5
 *
 * Copyright (c) 2008, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2008, Andy Dawson
 * @link          www.ad7six.com
 * @package       mi
 * @subpackage    mi.views.helpers
 * @since         v 1.0
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * MenuHelper class
 *
 * @uses          AppHelper
 * @package       mi
 * @subpackage    mi.views.helpers
 */
class MenuHelper extends AppHelper {

/**
 * name property
 *
 * @var string 'Menu'
 * @access public
 */
	public $name = 'Menu';

/**
 * helpers property
 *
 * @var array
 * @access public
 */
	public $helpers = array('Html');

/**
 * counter property
 *
 * @var int 1000
 * @access protected
 */
	protected $_counter = 1000;

/**
 * defaultSettings property
 *
 * @var array
 * @access protected
 */
	protected $_defaultSettings = array(
		'activeMode' => 'url', // url // controller[name] // action[and controller name] // false [do nothing]
		'hereMode' => 'active', // active[mark the li as active] // text[no link just text] // false[do nothing]
		'parentHereMode' => 'active', // active[mark the li as active] // text[no link just text] // false[do nothing]
		'hereKey' => null, // the key for the item to mark as active automatic based on activeMode if not specified
		'order' => null, // the order the whole section should be output. only used if generating many menus at once
		'uniqueKey' => 'title', // determins how data is stored internally, and how duplicate items are detected
		'overwrite' => false, // Overwrite the menu item if it already has been defined?
		'showWarnings' => true, // Trigger an error if trying to redefine a menu item and overwrite is false
		'headerTag' => false, // used to automatically wrap the section name in a (e.g.) h3 tag on display
		'typeTag' => 'ul', // The tag used for the menu links as a whole.
		'itemTag' => 'li', // The tag used for each menu link
		'wrap' => false, // a sprintf string to wrap the output of the menu e.g. "<div>%s</div>"
		'class' => 'menu', // the class attribute for the top level
		'id' => false, // the id attribute for the top level
		'splitCount' => false, // inject </ul><ul> after this number of menu items
	);

/**
 * settings property
 *
 * @var array
 * @access public
 */
	public $settings = array();

/**
 * section property
 *
 * The current section
 *
 * @var string 'menu'
 * @access protected
 */
	protected $_section = 'menu';

/**
 * data property
 *
 * Holds the menu data as they get built. References flatData.
 *
 * @var array
 * @access protected
 */
	protected $_data = array();

/**
 * flatData property
 *
 * A flat list of menu data
 *
 * @var array
 * @access protected
 */
	protected $_flatData = array();

/**
 * here property
 *
 * Place holder for router normalized "here"
 *
 * @var string ''
 * @access protected
 */
	protected $_here = '';

/**
 * construct method
 *
 * @param array $options
 * @return void
 * @access protected
 */
	public function __construct($options = array()) {
		$this->_defaultSettings = am($this->_defaultSettings, $options);
		parent::__construct($options);
	}

/**
 * beforeLayout method
 *
 * @return void
 * @access public
 */
	public function beforeLayout() {
		$this->_counter = 0;
	}

/**
 * Add a menu item.
 *
 * Add a menu item syntax examples:
 * 	$menu->add($title, $url); adds an entry with $title and $url to the current menu section
 * 	$menu->add('menu', $title, $url); add specifically to the 'menu' section
 * 	$menu->add('context', $title, $url); add an entry with $title and $url to the menu named "context"
 * 	$menu->add('context', $title, $url, 'subSection'); add an entry with $title and $url to subsection
 *  	"subSection" for the menu named "context"
 * 	$menu->add(array('url' => $url, 'title' => $title, 'options' => array('escapeTitle' => false)));
 * 		array syntax, not escaping title
 * 	$menu->add(array('url' => $url, 'title' => $title, 'htmlAttributes' => array('id' => 'foo'));
 *  	array syntax, setting id for the link, not the li
 *
 * @param string $section
 * @param mixed $title
 * @param mixed $url
 * @param mixed $under
 * @param array $options
 * @access public
 * @return void
 */
	public function add($section = null, $title = null, $url = null, $under = null, $options = array()) {
		$class = $id = null;
		$htmlAttributes = isset($options['htmlAttributes'])?$options['htmlAttributes']:array();
		$confirmMessage = false;
		$escapeTitle = true;
		$order = $this->_counter++;
		if (is_array($section)) {
			$first = current($section);
			if (is_array($first) && isset($first['title'])) {
				foreach ($section as $row) {
					$this->add($row);
				}
				return;
			} else {
				extract(am(array('section' => $this->_section), $section));
			}
		} elseif ($url == null) {
			if ($under) {
				$options = $under;
			}
			$under = $url;
			$url = $title;
			$title = $section;
			$section = $this->_section;
		}
		$settings = $this->settings($section);
		$section = $this->_section;
		if ($settings['uniqueKey'] === 'url') {
			$key = Router::normalize($url);
		} else {
			$key = $title;
		}
		if (is_array($under)) {
			if ($settings['uniqueKey'] === 'url') {
				$under = Router::normalize($under);
			}
		}
		if ($under === $key) {
			if ($settings['showWarnings'])  {
				trigger_error ('MenuHelper::add<br />' . $key . ' Menu item cannot have itself as its own parent');
			}
			return;
		}

		list($here, $markActive, $url) = $this->_setHere($section, $url, $key, $settings['activeMode'], $settings['hereMode'], $options);
		if ($options) {
			extract($options);
		}
		$item = array(
			'here' => $here,
			'order' => $order,
			'markActive' => $markActive,
			'url' => $url,
			'title' => $title,
			'under' => $under,
			'id' => $id,
			'class' => $class,
			'inPath'=> false,
			'sibling' => false,
			'children' => array(),
			'htmlAttributes' => $htmlAttributes,
			'confirmMessage' => false,
			'escapeTitle' => true
		);
		if ($under) {
			if (!isset($this->_flatData[$section][$under])) {
				$parent = array(
					'placeholder' => true,
					'order' => $order,
					'here' => false,
					'markActive' => false,
					'url' => null,
					'title' => null,
					'under' => false,
					'id' => null,
					'class' => null,
					'inPath'=> false,
					'sibling' => false,
					'children' => array(),
					'htmlAttributes' => array(),
					'confirmMessage' => false,
					'escapeTitle' => true
				);
				if ($settings['uniqueKey'] === 'title') {
					$parent[$settings['uniqueKey']] = $under;
				} else {
					$parent[$settings['uniqueKey']] = Router::normalize($under);
				}
				$this->_flatData[$section][$under] = $parent;
				$this->_data[$section][$under] =& $this->_flatData[$section][$under];
			}
			$this->_flatData[$section][$key] = $item;
			$this->_flatData[$section][$under]['children'][$key] =& $this->_flatData[$section][$key];
		} elseif (isset($this->_flatData[$section][$key]) && !empty($this->_flatData[$section][$key]['placeholder'])) {
			$item['children'] =& $this->_flatData[$section][$key]['children'];
			unset($this->_data[$section][$key]);
			unset($this->_flatData[$section][$key]);
			$this->_flatData[$section][$key] = $item;
			$this->_data[$section][$key] =& $this->_flatData[$section][$key];
		} elseif (!isset($this->_flatData[$section][$key]) || $settings['overwrite']) {
			$this->_flatData[$section][$key] = $item;
			$this->_data[$section][$key] =& $this->_flatData[$section][$key];
		} elseif ($settings['showWarnings'])  {
			if ($settings['uniqueKey'] === 'title') {
				$altKey = 'url';
			} else {
				$altKey = 'title';
			}
			trigger_error ('MenuHelper::add<br /> Duplicate menu item detected for item "' . $title .
				'" in menu "' . $section . '".<br />You can change the uniqueKey field used to detect duplicates' .
				' which is currently set to ' . $settings['uniqueKey'] . ', can be changed to ' . $altKey . '.');
		} else {
			return;
		}
		if ($settings['hereMode'] === 'text' && $here === true) {
			$this->_flatData[$section][$key]['url'] = false;
		}
		if (!empty($children)) {
			foreach ($children as $row) {
				$row['under'] = $key;
				$this->add($row);
			}
		}
	}

/**
 * addAttribute method
 *
 * @param mixed $tag
 * @param string $id
 * @param string $key
 * @param mixed $value
 * @return void
 * @access public
 */
	public function addAttribute($tag, $id = '', $key = '', $value = null) {
		if (!is_null($value)) {
			$this->_attributes[$tag][$id][$key] = $value;
		} elseif (!(isset($this->_attributes[$tag][$id]) && in_array($key, $this->_attributes[$tag][$id]))) {
			$this->_attributes[$tag][$id][] = $key;
		}
	}

/**
 * del method
 *
 * Delete a menu item. Specify the section name alone to delete the entire section.
 * Specify the section and key to delete a single menu item.
 * Specify just the key to delete an entry from the currently active menu section
 *
 * @param mixed $section
 * @param mixed $key
 * @return void
 * @access public
 */
	public function del($section, $key = null) {
		if (is_null($key)) {
			if (isset($this->_flatData[$section])) {
				unset ($this->_flatData[$section]);
				unset ($this->_data[$section]);
				return;
			}
			$key = $section;
			$section = $this->_section;
		}
		unset ($this->_flatData[$section][$key]);
		unset ($this->_data[$section][$key]);
	}

/**
 * display menu method
 *
 * display menu syntax examples:
 * 	echo $menu->display(); echo the currently active menu
 * 	echo $menu->displaydisplay('menu'); as above but explicit
 * 	echo $menu->display('menu', array('element' => 'menus/item'); use an element for each item's content
 * 	echo $menu->display('menu', array('callback' => 'menuItem'); use loose method menuItem for each item's content
 * 	echo $menu->display('menu', array('callback' => array(&$object, 'method'); call $object->method($data) for each item's content
 *
 * @param mixed $section the section name or the numerical order
 * @param array $settings to be passed to the tree helper
 * @param bool $createEmpty
 * @access public
 * @return void
 */
	public function display($section = null, $settings = array(), $createEmpty = true) {
		$this->setActive();
		if (is_array($section)) {
			extract(array_merge(array('section' => $this->_section), $section));
		}
		$settings = $this->settings($section, (array)$settings);
		if (!$section) {
			$section = $this->_section;
		}
		if (!isset($this->settings[$section]) || empty($this->_data[$section])) {
			$return = '';
		} else {
			$this->_attributes = array();
			$return = $this->_display($section, $settings, $this->_data[$section]);
		}
		if ($this->settings[$section]['wrap']) {
			$return = sprintf($this->settings[$section]['wrap'], $return);
		}
		if (trim($return) === '' && $createEmpty) {
			$typeTag = $this->settings[$section]['typeTag'];
			$return = $this->_displayHead($section, $settings, true) . "</$typeTag>";
		}
		unset ($this->settings[$section]);
		unset ($this->_data[$section]);
		unset ($this->_flatData[$section]);
		return trim($return);
	}

/**
 * displayAll method
 *
 * @param array $settings
 * @param bool $createEmpty
 * @return void
 * @access public
 */
	public function displayAll($settings = array(), $createEmpty = true) {
		$return = '';
		foreach($this->sections() as $section) {
			$return .= $this->display($section, $settings, $createEmpty);
		}
		return $return;
	}

/**
 * sections method
 *
 * Return the names of all sections currently stored by the helper, in the order they should be processed
 *
 * @access public
 * @return mixed array of menu sections if no order passed. name of the section name matching the order if passed.
 */
	public function sections($order = null) {
		$sequence = array();
		foreach ($this->settings as $key => $settings) {
			if ($order !== null && $settings['order'] == $order) {
				return $key;
			} elseif (!isset($sequence[$settings['order']])) {
				$sequence[$settings['order']] = $key;
			} else {
				$sequence[$settings['order'] . rand()] = $key;
			}
		}
		if ($order !== null) {
			return false;
		}
		ksort($sequence);
		return $sequence;
	}

/**
 * settings method
 *
 * @param mixed $section
 * @param array $settings
 * @return void
 * @access public
 */
	public function settings($section = null, $settings = array()) {
		if (is_array($section)) {
			$settings = $section;
			$section = null;
		}
		if ($section === null) {
			$section = $this->_section;
		} elseif (!$section) {
			$section = $this->_section = 'menu';
		} else {
			$this->_section = $section;
		}
		if (!$this->_here) {
			$view =& ClassRegistry:: getObject('view');
			if ($view) {
				$this->_here = $this->url($view->passedArgs);
			}
		}
		if (!isset($this->settings[$section])) {
			$settings = array_merge($this->_defaultSettings, $settings);
			$this->settings[$section] = $settings;
		} elseif ($settings) {
			$this->settings[$section] = array_merge($this->settings[$section], $settings);
		}
		if (!is_numeric($this->settings[$section]['order'])) {
			$this->settings[$section]['order'] = count($this->settings);
		}
	       return $this->settings[$section];
	}

	public function setActive($key = null, $section = null) {
		if ($section === null) {
			$section = $this->_section;
		}
		$settings = $this->settings($section);
		if ($key) {
			if (isset($this->_flatData[$section][$settings['hereKey']]['markActive'])) {
				$this->_flatData[$section][$settings['hereKey']]['markActive'] = false;
			}
		} elseif (isset($settings['hereKey'])) {
			$key = $settings['hereKey'];
		} else {
			return false;
		}
		$this->settings[$section]['hereKey'] = $key;
		if (!isset($this->_flatData[$section][$key])) {
			return false;
		}
		$this->_flatData[$section][$key]['markActive'] = true;
		if ($settings['parentHereMode'] && $this->_flatData[$section][$key]['under']) {
			$this->_setParentsActive(
				$key,
				$settings['parentHereMode'],
				$this->_flatData[$section]
			);
		}
	}
/**
 * attributes method
 *
 * @param mixed $rType
 * @param bool $clear
 * @return void
 * @access protected
 */
	protected function _attributes($tag, $clear = true) {
		if (empty($this->_attributes[$tag])) {
			return '';
		}
		foreach ($this->_attributes[$tag] as $i => &$values) {
			foreach ($values as $j => &$val) {
				if (is_array($val)) {
					$_a = array();
					foreach ($val as $k => &$v) {
						$_a[] = $k . ':' . $v;
					}
					$val = implode(';', $_a);
				}
				if (is_string($j)) {
					$val = $j . ':' . $val . ';';
				}
			}
			$values = $i . '="' . implode(' ', $values) . '"';
		}
		$return = ' ' . implode(' ', $this->_attributes[$tag]) . ' ';
		if ($clear) {
			unset($this->_attributes[$tag]);
		}
		return $return;
	}

/**
 * internal callback
 *
 * Used to return the output from the html helper using the parameters for this menu option
 *
 * @param mixed $data
 * @return void
 * @access protected
 */
	protected function _menuItem($data) {
		if ($data['markActive']) {
			if ($data['markActive'] === true) {
				$data['markActive'] = 'active';
			}
			$this->addAttribute($this->settings[$this->_section]['itemTag'], 'class', $data['markActive']);
		}
		if ($data['class']) {
			$this->addAttribute($this->settings[$this->_section]['itemTag'], 'class', $data['class']);
		}
		if ($data['id']) {
			$this->addAttribute($this->settings[$this->_section]['itemTag'], 'id', $data['id']);
		}

		if ($data['url'] === false) {
			return $data['title'];
		} else {
			return $this->Html->link($data['title'], $data['url'], $data['htmlAttributes'],
				$data['confirmMessage'], $data['escapeTitle']);
		}
	}

/**
 * display method
 *
 * Generate a menu. Works recurslively for nested menus
 *
 * @param mixed $section
 * @param mixed $settings
 * @param mixed $data
 * @return void
 * @access protected
 */
	protected function _display($section, $settings, $data, $header = true, $prefix = "\r\n") {
		$return = '';
		$start = true;
		if ($settings['splitCount']) {
			$total = count($data);
			$splitCount = $total / $settings['splitCount'];
			$rounded = (int)$splitCount;
			if ($rounded < $splitCount) {
				$splitCount = $rounded + 1;
			}
			$splitCounter = 0;
		}
		$typeTag = $settings['typeTag'];
		$itemTag = $settings['itemTag'];
		$_data = array();
		$data = array_reverse($data);
		foreach ($data as $i => $row) {
			$_data[$row['order']] = $row;
		}
		ksort($_data);
		$data = array_values($_data);
		foreach ($data as $i => &$result) {
			if ($settings['splitCount']) {
				if ($splitCounter && !($splitCounter % $splitCount) && $splitCounter != $total) {
					$return .= "$prefix</$typeTag><$typeTag>";
				}
				$splitCounter++;
			}
			$contents = $this->_menuItem($result);
			$attributes = $this->_attributes($itemTag);
			$return .= "$prefix\t<$itemTag{$attributes}>$contents";
			if (!empty($result['children'])) {
				$_settings = am($settings, array('class' => false, 'id' => false));
				$return .= $this->_display($section, $_settings, $result['children'], false, $prefix . "\t\t");
				$return .= $prefix . "\t";
			}
			$return .= "</$itemTag>";
			if ($start) {
				$start = false;
				$return = $prefix . $this->_displayHead($section, $settings, $header) . $return;
			}
		}
		$return .= "$prefix</$typeTag>";
		return $return;
	}

/**
 * displayHead method
 *
 * Optionally announce the start of this menu (create <h3>name of menu</h3>)
 * Generate a ul tag with appropriate attributes
 *
 * @param mixed $section
 * @param mixed $settings
 * @param bool $header
 * @return void
 * @access protected
 */
	protected function _displayHead($section, $settings, $header = false) {
		$return = '';
		if ($header) {
			$section = Inflector::humanize(Inflector::underscore($section));
			if (!empty($settings['headerTag'])) {
				$tag = $settings['headerTag'];
				$return .= "<$tag>$section</$tag>";
			}
			if (!empty($settings['class'])) {
				$this->addAttribute($settings['typeTag'], 'class', $settings['class']);
			}
			if (!empty($settings['id'])) {
				$this->addAttribute($settings['typeTag'], 'id', $settings['id']);
			}
		}
		$tag = $settings['typeTag'];
		$attributes = $this->_attributes($tag);
		$return .= "<$tag{$attributes}>";
		return $return;
	}

/**
 * setHere method
 *
 * Used internally to detect whether the current menu item links to the page currently
 * being rendered and modify the url if appropriate
 *
 * @param mixed $section
 * @param mixed $url
 * @param mixed $activeMode
 * @param mixed $hereMode
 * @access protected
 * @return array($here, $markActive, $url)
 */
	protected function _setHere($section, $url, $key, $activeMode, $hereMode, $options) {
		$view =& ClassRegistry:: getObject('view');
		if (!$view) {
			return array(false, false, $url);
		} elseif (isset($this->settings[$section]['hereKey'])) {
			if ($this->settings[$section]['hereKey'] == $key) {
				return array(true, true, $url);
			}
			return array(false, false, $url);
		}
		$here = $markActive = null;
		if (array_key_exists('here', $options)) {
			$here = $options['here'];
		}
		if (array_key_exists('markActive', $options)) {
			$markActive = $options['markActive'];
		}
		if ($here === null && $activeMode) {
			if ($activeMode == 'url' && $url && $this->url($url) == $this->_here) {
				$here = true;
			} elseif (!in_array($activeMode, array('action'))) {
				if (isset($view->passedArgs[$activeMode])) {
					$test = $view->passedArgs[$activeMode];
				} elseif (isset($view->params[$activeMode])) {
					$test = $view->params[$activeMode];
				} elseif (!isset($url[$activeMode])) {
					$here = true;
				} else {
					return array(false, false, $url);
				}
				if (isset($url[$activeMode]) && $test === $url[$activeMode]) {
					$fullUrl = $this->url($url, true);
					preg_match('@[^:]*://([^/]*).*@', $fullUrl, $matches);
					if (!empty($matches[1]) && $matches[1] === env('HTTP_HOST')) {
						$here = true;
					}
				}
			} elseif (is_array($url) &&
				(!isset($url['controller']) ||
					Inflector::underscore($url['controller']) == Inflector::underscore($view->name)))  {
				if ($activeMode == 'controller') {
					$here = true;
				} elseif ($activeMode == 'action' &&
					(!isset($url['action']) || $url['action'] == Inflector::underscore($view->action))) {
					$here = true;
				}
			}
		}
		if ($here) {
			$this->settings[$section]['hereKey'] = $key;
			if ($hereMode == 'text') {
				$url = false;
			} elseif ($hereMode && $markActive === null) {
				$markActive = true;
			}
		}
		return array($here, $markActive, $url);
	}

/**
 * setParentsActive method
 *
 * @param mixed $key
 * @param mixed $value
 * @param mixed $data
 * @return void
 * @access protected
 */
	protected function _setParentsActive($key, $value, &$data) {
		if (isset($data[$data[$key]['under']])) {
			$data[$data[$key]['under']]['markActive'] = $value;
			$this->_setParentsActive($data[$key]['under'], $value, $data);
		}
	}

/**
 * addm method
 *
 * @deprecated
 * @param string $section
 * @param array $data
 * @access public
 * @return void
 */
	public function addm($section = null, $data = array()) {
		if (is_array($section)) {
			return $this->add($section);
		}
		$this->_section = $section;
		return $this->add($data);
	}

/**
 *
 * addItemAttribute method
 *
 * @deprecated
 * @param string $id
 * @param string $key
 * @param mixed $value
 * @return void
 * @access public
 */
	public function addItemAttribute($id = '', $key = '', $value = null) {
		$this->addAttribute($this->settings[$this->_section]['itemTag'], $id, $key, $value);
	}

/**
 * addTypeAttribute method
 *
 * @deprecated
 * @param string $id
 * @param string $key
 * @param mixed $value
 * @return void
 * @access public
 */
	public function addTypeAttribute($id = '', $key = '', $value = null) {
		$this->addAttribute($this->settings[$this->_section]['typeTag'], $id, $key, $value);
	}

/**
 * internal callback
 *
 * @deprecated
 * @param array $data
 * @access public
 * @return void
 */
	public function menuItem(&$data) {
		return $this->_menuItem($data);
	}

/**
 * generate method
 *
 * @deprecated
 * @param mixed $section
 * @param array $settings
 * @param bool $createEmpty
 * @return void
 * @access public
 */
	public function generate($section = null, $settings = array(), $createEmpty = true) {
		return $this->display($section, $settings, $createEmpty);
	}
}