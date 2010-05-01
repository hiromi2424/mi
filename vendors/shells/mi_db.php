<?php
/**
 * MiDb is a shell for querying and manipulating your database
 *
 * MiDb is written such that you use your native db tools (e.g. mysqldump)
 * to generate schema and update scripts. It's experimental, but as all it does
 * in principle is construct the right params to call your db dump utility it's
 * quite robust/easy to debug/enhance etc.
 *
 * PHP version 5
 *
 * Copyright (c) 2009, Andy Dawson
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) 2009, Andy Dawson
 * @link          www.ad7six.com
 * @package       mi
 * @subpackage    mi.vendors.shells
 * @since         v 1.0 (30-Sep-2009)
 * @license       http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Model', 'ConnectionManager');

/**
 * MiDb is a shell for querying and manipulating your database
 *
 * MiDbShell class
 *
 * @uses          Shell
 * @package       mi
 * @subpackage    mi.vendors.shells
 */
class MiDbShell extends Shell {

/**
 * name property
 *
 * @var string 'MiDb'
 * @access public
 */
	public $name = 'MiDb';

/**
 * name property
 *
 * @var string 'mi_db'
 * @access protected
 */
	protected $_name = 'mi_db';

/**
 * version property
 *
 * @var string '0.2'
 * @access protected
 */
	protected $_version = '0.2';

/**
 * settings property
 *
 * @var array
 * @access public
 */
	public $settings = array(
	);

/**
 * defaultSettings property
 *
 * @var array
 * @access protected
 */
	protected $_defaultSettings = array(
		'-connection' => 'default',
		'-model' => '',
		'-table' => '',
		'-dry-run' => false,
		'-quiet' => false,
		'-interactive' => true,
		'extraOptions' => '',
		'commands' => array(),
	);

/**
 * commandOptions property
 *
 * Stub - store description for paramters - allow for storing anythign else (like allowed values)
 *
 * @var array
 * @access protected
 */
	protected $_commandOptions = array(
		'-help' => array(
			'description' => 'Don\'t do anything just show me help (safe)',
			'short' => 'h'
		),
		'-interactive' => array(
			'description' => 'Defaults to true|false - ask before doing anything',
			'short' => 'i'
		),
		'-force' => array(
			'description' => 'Don\'t ask questions - just do it (risky)',
			'short' => 'f'
		),
		'-dry-run' => array(
			'description' => 'Go through the motions but don\'t commit results (safe - @TODO WHEN implemented)',
			'short' => 'n'
		),
		'-quiet' => array(
			'description' => 'Supress none-error output',
			'short' => 'q'
		),
		'-verbose' => array(
			'description' => 'Display more details - can be specified as a numeric value',
			'short' => 'v'
		)
	);

/**
 * shortOptions property
 *
 * @var array
 * @access protected
 */
	protected $_shortOptions = array(
		// 'd' => '-debug', Hidden, but should exist/be supported?
		'h' => '-help',
		'i' => '-interactive',
		'f' => '-force',
		'n' => '-dry-run',
		'q' => '-quiet',
		'v' => '-verbose',
	);

/**
 * commands property
 *
 * @var array
 * @access protected
 */
	protected $_commands = array(
		'mysql' => array(
			'connection' => '--host=:host --port=:port --user=:login --password=:password --default-character-set=:encoding',
			'connect' => 'mysql :connection :database',
			'copy' => ':export | :import',
			'standardOptions' => '--set-charset -e',
			'dump' => 'mysqldump :connection -d -R :standardOptions :extraOptions :database :-table',
			'dumpComplete' => 'mysqldump :connection -R -C -e :standardOptions :extraOptions :database :-table',
			'dumpCreate' => 'mysqldump :connection -d -R -C --add-drop-table :standardOptions :extraOptions :database :-table',
			'dumpData' => 'mysqldump :connection -t -C -e :standardOptions :extraOptions :database :-table',
			'dumpRoutines' => 'mysqldump :connection -d -t -R -C :standardOptions :extraOptions :database :-table',
			'import' => 'mysql :connection :extraOptions --database=:database :-table < :file',
			'importCompressed' => ':uncompress :file | mysql :connection :extraOptions --database=:database :-table',
			'diff' => 'diff -u -w :from :to',
			'stripAutoIncrement' => 'sed -i "s/ AUTO_INCREMENT=[0-9]\+//" :file',
			'stripComments' => 'sed -i -e "/^--/d" -e "/^$/d" :file',
		)
	);

/**
 * help method
 *
 * @return void
 * @access public
 */
	public function help()  {
		$exclude = array('main');
		$shell = get_class_methods('Shell');
		$methods = get_class_methods($this);
		$methods = array_diff($methods, $shell);
		$methods = array_diff($methods, $exclude);

		$this->out($this->name . ' Shell. Version ' . $this->_version);
		switch ($this->command) {
			case 'copy':
				$this->out('MiDb copy.');
				$this->out('Move data from one db connection to another');
				$this->hr();
				$this->out('Usage: cake ' . $this->name . ' copy fromThisConnection toThisConnection');
				$this->out('       cake ' . $this->name . ' copy -from fromThisConnection -to toThisConnection');
				$this->out('       cake ' . $this->name . ' copy -from fromThisConnection -to toThisConnection -table justthistable');
				$this->out('');
				$this->out('The copy command allows you to copy a whole db from one connection to another');
				$this->out('It issues a dump (which includes drop and create tables) and pipes it directly');
				$this->out('   to the import of the target connection');
				break;
			case 'init':
				$this->out('Initialize your database');
				$this->out();
				$this->out('Import an sql file, or run the default sql file for this application');
				$this->out();
				$this->out("Usage: cake {$this->_name} init");
				$this->out("  or   cake {$this->_name} init --file=/this/specific/file.sql");
				$this->out('');
				$this->out('Options');
				$this->out('    --file=file.sql           If the file isn\'t specified config/schema/default.sql');
				$this->out('                              will be loaded if it exists, otherwise the default');
				$this->out('                              schema from mi_development (if found) will be loaded');
				$this->out('');
				break;
			default:
				foreach ($methods as $method) {
					if (!isset($help[$method]) && $method[0] !== '_') {
						$help[] = $method;
					}
				}
				$this->out($this->name . ' is a shell for manipulating database structures and data');
				$this->out('');
				$this->out('MiDb allows you to easily generate (sql) schema files and data dumps, as well as');
				$this->out(' making it easier to import sql files from other developers/users');
				$this->out('');
				foreach($help as $i => $message) {
					if (!$i) {
						$this->out("Usage: cake {$this->_name} $message <options> <args>");
					} else {
						$this->out("  or   cake {$this->_name} $message <options> <args>");
					}
				}
				$this->out('');
				$this->out('Options');
				foreach($this->_shortOptions as $short => $long) {
					if (!empty($this->_commandOptions[$long]['description'])) {
						$description = $this->_commandOptions[$long]['description'];'<description>';
					} else {
						$description = '<description>';
					}
					$this->out(str_pad(" -$short, -$long ", 30) . $description);
				}
				$this->out('');
				$this->out('Append --help (or -h) to get help for specific help for that function. e.g.:');
				$this->out("cake {$this->_name} example foo bar --help");

		}
		$this->hr();
	}

/**
 * startup method
 *
 * @return void
 * @access public
 */
	public function startup() {
		$this->_welcome();
		$this->db =& ConnectionManager::getDataSource($this->settings['-connection']);
		$name = $this->db->config['driver'];
		if (!isset($this->settings['commands'][$name])) {
			$this->settings['commands'][$name] = $this->_commands[$name];
		} else {
			$this->settings['commands'][$name] = array_merge($this->_commands[$name], $this->settings['commands'][$name]);
		}
	}

/**
 * initialize method
 *
 * @return void
 * @access public
 */
	public function initialize() {
		$this->_handleOptions();
		$this->settings = array_merge($this->_defaultSettings, array_intersect_key($this->params, $this->_defaultSettings));

		if (file_exists('config' . DS . $this->_name . '.php')) {
			include('config' . DS . $this->_name . '.php');
			if (!empty($config)) {
				$this->settings = am($this->settings, $config);
			}
		} elseif (file_exists(APP . 'config' . DS . $this->_name . '.php')) {
			include(APP . 'config' . DS . $this->_name . '.php');
			if (!empty($config)) {
				$this->settings = am($this->settings, $config);
			}
		}

		if (!empty($this->params['-help'])) {
			$this->help();
			return $this->_stop();
		}

		if (!empty($this->settings['-force'])) {
			$this->settings['-interactive'] = false;
		}

		if (!empty($this->settings['-model'])) {
			$connecitons = $tables = array();
			foreach((array)$this->params['-model'] as $model) {
				$Model = ClassRegistry::init($model);
				$connections[$Model->useDbConfig] =& ConnectionManager::getDataSource($Model->useDbConfig);
				$tables[] = $connections[$Model->useDbConfig]->fullTableName($Model, false);
			}
			if (count($connections) !== 1) {
				return trigger_error('MiDbShell:: mixed connections are not supported when dumping tables');
			}
			$this->settings['-connection'] = key($connections);
			$this->settings['-table'] = implode(' ', array_unique($tables));
		}

		/* @TODO
		$extraParams = array();
		foreach($this->params as $k => $v) {
			if ($k[0] !== '-') {
				continue;
			}
			$k = '-' . $k;
			if ($v != 1) {
				$k .= '=' . $v;
			}
			$extraParams[] = $k;
		}
		if ($extraParams) {
			$this->settings['extraOptions'] = implode($extraParams, ' ');
		}
		 */
		if (empty($this->_commands['mysqli'])) {
			$this->_commands['mysqli'] = $this->_commands['mysql'];
		}
	}

/**
 * init method
 *
 * @return void
 * @access public
 */
	public function init() {
	}

/**
 * main method
 *
 * @return void
 * @access public
 */
	public function main() {
		return $this->help();
	}

/**
 * backup method
 *
 * @return void
 * @access public
 */
	public function backup() {
		$settings = array();
		if (empty($this->settings['toFile'])) {
			$settings['toFile'] = $this->_backupName(CONFIGS . 'schema' . DS . 'backups' . DS . $this->settings['-connection']);
			if (isset($this->args[0])) {
				$settings['toFile'] .= '_' . Inflector::underscore($this->args[0]);
			}
			$settings['toFile'] .= '.sql';
		}
		$this->_run('backup', 'dump', null, $settings);

		if (!empty($this->params['bz2'])) {
			$this->_exec('gzip -f ' . $settings['toFile'], $out);
			$target = $settings['toFile'] . '.gz';
		} elseif (!empty($this->params['gzip'])) {
			$this->_exec('bzip2 -f ' . $settings['toFile'], $out);
			$target = $settings['toFile'] . '.bz2';
		} elseif (!empty($this->params['zip'])) {
			$this->_exec('zip -rj ' . $settings['toFile'] . '.zip ' . $settings['toFile'], $out);
			$target = $settings['toFile'] . '.zip';
		}

		if (!empty($target) && file_exists($target)) {
			if (empty($this->settings['quiet'])) {
				$this->out($out);
				$this->out();
			}
			$this->out($target);
		}
	}

/**
 * save method
 *
 * @return void
 * @access public
 */
	public function save() {
		$settings = array();
		if (empty($settings['toFile'])) {
			$settings['toFile'] = CONFIGS . 'schema' . DS . $this->settings['-connection'];
			if (isset($this->args[0])) {
				$settings['toFile'] .= '_' . Inflector::underscore($this->args[0]);
			}
			$settings['toFile'] .= '.sql';
		}
		$this->_run('save', 'dump', null, $settings);
		$this->stripAutoIncrement($settings);
		$this->stripComments($settings);
	}

/**
 * stripAutoIncrement method
 *
 * @param array $settings array()
 * @return void
 * @access public
 */
	public function stripAutoIncrement($settings = array()) {
		if (!empty($settings['toFile'])) {
			$file = $settings['toFile'];
		} else {
			if (isset($this->params['file'])) {
				$file = $this->params['file'];
			} elseif (!empty($this->args[0])) {
				$file = $this->args[0];
			} else {
				$file = CONFIGS . 'schema' . DS . $this->settings['-connection'];
				if (isset($this->args[0])) {
					$file .= '_' . Inflector::underscore($this->args[0]);
				}
				$file .= '.sql';
			}
		}
		$settings['file'] = $file;
		$settings['toFile'] = false;
		$this->_run('strip auto increment', 'stripAutoIncrement', null, $settings);
	}

/**
 * stripComments method
 *
 * @param array $settings array()
 * @return void
 * @access public
 */
	public function stripComments($settings = array()) {
		if (!empty($settings['toFile'])) {
			$file = $settings['toFile'];
		} else {
			if (isset($this->params['file'])) {
				$file = $this->params['file'];
			} elseif (!empty($this->args[0])) {
				$file = $this->args[0];
			} else {
				$file = CONFIGS . 'schema' . DS . $this->settings['-connection'];
				if (isset($this->args[0])) {
					$file .= '_' . Inflector::underscore($this->args[0]);
				}
				$file .= '.sql';
			}
		}
		$settings['file'] = $file;
		$settings['toFile'] = false;
		$this->_run('strip comments', 'stripComments', null, $settings);
	}

/**
 * copy method
 *
 * @return void
 * @access public
 */
	public function copy() {
		$from = $to = null;
		if (!empty($this->params['from'])) {
			$from = $this->params['from'];
		}
		if (!empty($this->params['to'])) {
			$to = $this->params['to'];
		}
		if (empty($from) || empty($to)) {
			if (count($this->args) >= 2) {
				list($from, $to) = $this->args;
			} else {
				return $this->help();
			}
		}

		$fromDb =& ConnectionManager::getDataSource($from);
		$name = $fromDb->config['driver'];
		$command = 'dump';
		// Allow for filters in the future
		$this->_commandNameSuffix($command, 'complete', $this->settings);
		$command = $this->settings['commands'][$name][$command];
		$dump = $this->_command($command, $fromDb->config, $name, $this->settings);

		$toDb =& ConnectionManager::getDataSource($to);

		if ($fromDb->config === $toDb->config) {
			$this->err("$from and $to are the same database. Stopping, no action taken");
			return $this->_stop();
		}

		$name = $toDb->config['driver'];
		$command = str_replace(' :-table < :file', '', $this->settings['commands'][$name]['import']);
		$import = $this->_command($command, $toDb->config, $name, $this->settings);

		$command = "$dump | $import";
		if (empty($this->settings['quiet'])) {
			$this->out("Copying tables from $from to $to");
		}
		return $this->_out($command, $this->settings);
	}

	public function connectString() {
		$settings = $this->settings;

		$commandName = 'connect';

		$db =& ConnectionManager::getDataSource($settings['-connection']);
		$name = $this->db->config['driver'];

		$config = $db->config;

		if (!isset($settings['commands'][$name][$commandName])) {
			return $this->err("no command defined for $commandName");
		}
		$command = $settings['commands'][$name][$commandName];
		$command = $this->_command($command, $config, $name, $settings);

		$this->out($command);
	}

/**
 * dump method
 *
 * @return void
 * @access public
 */
	public function dump() {
		$this->_run('dump');
	}

/**
 * import method
 *
 * @return void
 * @access public
 */
	public function import() {
		$file = '';
		if (isset($this->params['file'])) {
			$file = $this->params['file'];
		} elseif (!empty($this->args[0])) {
			$file = $this->args[0];
		}
		if (!is_file($file)) {
			if ($file) {
				$file = '_' . $file;
			}
			$file = CONFIGS . 'schema' . DS . $this->settings['-connection'] . $file . '.sql';
		}
		if (empty($this->params['force'])) {
			$this->out(file_get_contents($file, null, null, 0, 1000) . '...');
			$continue = strtoupper($this->in("Import $file into {$this->settings['-connection']}?", array('Y', 'N')));
			if ($continue !== 'Y') {
				$this->out('Import aborted');
				return $this->_stop();
			}
		}
		$settings['file'] = $file;
		$meta = pathinfo($file);
		if ($meta['extension'] !== 'sql') {
			$settings['compress'] = $meta['extension'];
		}
		if (!empty($settings['compress'])) {
			if ($settings['compress'] === 'gz') {
				$settings['uncompress'] = 'gzip -dc';
			} elseif ($settings['compress'] === 'bz2') {
				$settings['uncompress'] = 'bzip2 -dc';
			}
			$this->_run('import', 'importCompressed', false, $settings);
			return;
		}
		$this->_run('import', 'import', false, $settings);
	}

/**
 * compare method
 *
 * @return void
 * @access public
 */
	public function compare() {
		$to = '_current_';
		if (!empty($this->args[1])) {
			$from = $this->args[0];
			$to = $this->args[1];
		} elseif (!empty($this->args[0])) {
			$from = $this->args[0];
		} else {
			$from = '';
		}
		if ($to === '_current_') {
			$to = TMP . 'to.sql';
			$this->_run('dump', 'dump', false, array('toFile' => $to));
		}
		if (!is_file($from)) {
			if ($from) {
				$from = '_' . $from;
			}
			$from = CONFIGS . 'schema' . DS . $this->settings['-connection'] . $from . '.sql';
			if (!is_file($from)) {
				return trigger_error('MiDbShell:: ' . $from . ' not found, cannot compare schemas');
			}
		}
		copy($from, TMP . 'from.sql');
		$from = TMP . 'from.sql';
		$settings = compact('to', 'from');
		$settings['debug'] = true;
		$settings['return'] = true;
		$result = $this->_run('diff', 'diff', false, $settings);
		foreach($result as $i => $line) {
			if (strpos('-- ', $line) === 0) {
				unset($result[$i]);
			}
		}
		debug ($result); //@ignore
	}

/**
 * run method
 *
 * @param string $friendlyName ''
 * @param mixed $commandName null
 * @return void
 * @access protected
 */
	protected function _run($friendlyName = '', $commandName = null, $version = null, $settings = array()) {
		$settings = array_merge($this->settings, $settings);
		if (!$commandName) {
			$commandName = $friendlyName;
		}
		$db =& ConnectionManager::getDataSource($settings['-connection']);
		$name = $this->db->config['driver'];

		$version = $this->_commandNameSuffix($commandName, $version, $settings);
		if ($version) {
			$friendlyName .= $version;
		}
		$config = $db->config;

		if (!isset($settings['commands'][$name][$commandName])) {
			return $this->err("no command defined for $commandName");
		}
		$command = $settings['commands'][$name][$commandName];
		$command = $this->_command($command, $config, $name, $settings);
		if (empty($this->settings['quiet'])) {
			$this->out("Running $friendlyName");
		}
		return $this->_out($command, $settings);
	}

/**
 * command method
 *
 * @param mixed $string
 * @param mixed $replacements
 * @param mixed $name
 * @return void
 * @access protected
 */
	protected function _command($string, $replacements, $name, $settings = array()) {
		$settings = array_merge($this->settings, $settings);
		$replacements = am($settings, $replacements, $settings['commands'][$name]);
		foreach($replacements as $key => &$value) {
			if (stripos('file', $key) !== false) {
				$value = escapeshellarg($value);
			}
		}
		$check = $return = $string;
		do {
			$check = $return;
			$return = String::insert($return, $replacements);
		} while ($check !== $return);
		return preg_replace('@\s+@', ' ', $return);
	}

/**
 * out method
 *
 * @param mixed $command
 * @return void
 * @access protected
 */
	protected function _out($command, $settings = array()) {
		$settings = array_merge($this->settings, $settings);
		if (!empty($settings['-debug'])) {
			$this->out($command);
		}
		if (!empty($settings['return'])) {
			$this->_exec($command, $return);
			return $return;
		}
		if (empty($settings['toFile'])) {
			$out = `$command`;
			if (empty($this->settings['quiet'])) {
				$this->out($out);
			}
		} else {
			if (empty($this->settings['quiet'])) {
				$this->out('generating ' . $settings['toFile']);
			}
			$command .= ' > ' . escapeshellarg($settings['toFile']);
			`$command`;
		}
	}

/**
 * backupName method
 *
 * @param mixed $name
 * @return void
 * @access protected
 */
	protected function _backupName($name) {
		$name .= '_' . date('ymd-H') . str_pad((int)(date('i') / 10) * 10, 2, '0');
		$dir = dirname($name);
		if (!is_dir($dir)) {
			new Folder($dir, true);
		}
		return $name;
	}

/**
 * commandNameSuffix method
 *
 * @param mixed $commandName
 * @param mixed $version
 * @param array $settings array()
 * @return void
 * @access protected
 */
	protected function _commandNameSuffix(&$commandName, $version, &$settings = array()) {
		$settings = array_merge($this->settings, $settings);
		if ($version === null) {
			if ($this->args) {
				$version = $this->args[0];
			}
		}
		if ($version) {
			$return = ucfirst(Inflector::camelize($version));
			$commandName .= $return;
			return $return;
		}
		return '';
	}

/**
 * If this class needs to make exec calls - make them via the Mi::exec
 * funciton so they can be logged/handled consistently
 *
 * @param mixed $cmd
 * @param mixed $out null
 * @return void
 * @access protected
 */
	protected function _exec($cmd, &$out = null) {
		if (!class_exists('Mi')) {
			App::import('Vendor', 'Mi.Mi');
		}
		return Mi::exec($cmd, $out);
	}

/**
 * handleOptions method
 *
 * Convert usage of short params (e.g. -h) to long params (e.g. --help)
 * allow more typical --option=value syntax
 * allow arrays to be specified as comma seperated lists:
 *  --option=this,that,other becomes array('this', 'that', 'other')
 *
 * @return void
 * @access protected
 */
	protected function _handleOptions() {
		$shortOptions = array();
		foreach($this->_commandOptions as $option => $details) {
			if (!empty($details['short'])) {
				$shortOptions[$details['short']] = $option;
			}
		}
		$shortArgs = array_intersect_key($shortOptions, $this->params);
		foreach($shortArgs as $short => $real) {
			$this->params[$real] = $this->params[$short];
			unset ($this->params[$short]);
		}
		foreach($this->params as $key => &$value) {
			if (strpos($key, '=')) {
				list($realKey, $value) = explode('=', $key);
				if (strpos($value, ',')) {
					$value = explode(',', $value);
				}
				$this->params[$realKey] = $value;
				unset ($this->params[$key]);
				continue;
			}
			if (is_string($value) && strpos($value, ',')) {
				$value = explode(',', $value);
			}
		}
	}

/**
 * Don't send the welcome text in quiet mode
 *
 * @TODO must be public because it overrides a public function
 * @return void
 * @access protected
 */
	public function _welcome() {
		if ($this->settings['-quiet']) {
			return;
		}
		parent::_welcome();
	}
}