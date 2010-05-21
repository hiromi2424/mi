<?php
/**
 * Language Component
 *
 * Automatically sets the default language if not set and adds plugins to the 'global' locale
 * paths if so configured. This permits using for example:
 *
 * 		__d('someplugin_foo', 'bar')
 *
 * in your code and for that to automatically find
 *
 *  plugins/someplugin/locales/abc/LC_MESSAGES/someplugin_foo.po
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
 * @subpackage    mi.controllers.components
 * @since         v 1.0 (21-May-2010)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * LanguageComponent class
 *
 * @uses          Object
 * @package       mi
 * @subpackage    mi.controllers.components
 */
class LanguageComponent extends Object {

/**
 * initialize method
 *
 * Merge settings and set Config.language to a valid locale
 *
 * @return void
 * @access public
 */
	function initialize(&$Controller, $config = array()) {
		App::import('Vendor', 'Mi.MiCache');
		$lang = MiCache::setting('Site.lang');
		if (!$lang) {
			if (!defined('DEFAULT_LANGUAGE')) {
				return;
			}
			$lang = DEFAULT_LANGUAGE;
		} elseif (!defined('DEFAULT_LANGUAGE')) {
			define('DEFAULT_LANGUAGE', $lang);
		}
		Configure::write('Config.language', $lang);
		App::import('Core', 'I18n');
		$I18n =& I18n::getInstance();
		$I18n->domain = 'default_' . $lang;
		$I18n->__lang = $lang;
		$I18n->l10n->get($lang);

		if (!empty($Controller->plugin)) {
			$config['plugins'][] = Inflector::underscore($Controller->plugin);
		}
		if (!empty($config['plugins'])) {
			$plugins = array_intersect(MiCache::mi('plugins'), $config['plugins']);
			$Inst = App::getInstance();
			foreach($plugins as $path => $name) {
				$Inst->locales[] = $path . DS . 'locale' . DS;
			}
		}
	}
}