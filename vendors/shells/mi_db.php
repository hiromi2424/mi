<?php
/**
 * Short description for mi_db.php
 *
 * Long description for mi_db.php
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
 * version property *
 * @var string '0.1'
 * @access protected
 */
	protected $version = '0.1';

/**
 * settings property
 *
 * @var array
 * @access public
 */
	public $settings = array(
		'extraOptions' => '',
		'connection' => 'default',
		'table' => '',
		'quiet' => false,
		'commands' => array(),
	);

/**
 * commands property
 *
 * @var array
 * @access protected
 */
	protected $commands = array(
		'mysql' => array(
			'connection' => '--host=:host --port=:port --user=:login --password=:password --default-character-set=:encoding',
			'copy' => ':export | :import',
			'standardOptions' => '--set-charset -e',
			'dump' => 'mysqldump :connection -d -R :standardOptions :extraOptions :database :table',
			'dumpComplete' => 'mysqldump :connection -R -C -e :standardOptions :extraOptions :database :table',
			'dumpCreate' => 'mysqldump :connection -d -R -C --add-drop-table :standardOptions :extraOptions :database :table',
			'dumpData' => 'mysqldump :connection -t -C -e :standardOptions :extraOptions :database :table',
			'dumpRoutines' => 'mysqldump :connection -d -t -R -C :standardOptions :extraOptions :database :table',
			'import' => 'mysql :connection :extraOptions --database=:database :table < :file',
			'importCompressed' => ':uncompress :file | mysql :connection :extraOptions --database=:database :table',
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
		switch ($this->command) {
			case 'copy':
				$this->out('Usage: cake ' . $this->name . ' copy fromThisConnection toThisConnection');
				$this->out('       cake ' . $this->name . ' copy -from fromThisConnection -to toThisConnection');
				$this->out('       cake ' . $this->name . ' copy -from fromThisConnection -to toThisConnection -table justthistable');
				$this->out('');
				$this->out('The copy command allows you to copy a whole db from one connection to another');
				$this->out('It issues a dump (which includes drop and create tables) and pipes it directly');
				$this->out('   to the import of the target connection');
				break;
			default:
				foreach ($methods as $method) {
					if (!isset($help[$method]) && $method[0] !== '_') {
						$help[$method] = $method;
					}
				}
				$this->out('Usage: cake ' . $this->name . ' command');
				$this->out('');
				$this->out($this->name . ' is a shell for manipulating database structures and data');
				$this->out('');
				$this->out('Commands:');
				foreach($help as $message) {
					$this->out("\t" . $message);
				}
		}
		$this->out('');
		$this->out('Use the -v flag to see what commands are being issued');
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
		$this->db =& ConnectionManager::getDataSource($this->settings['connection']);
		$name = $this->db->config['driver'];
		if (!isset($this->settings['commands'][$name])) {
			$this->settings['commands'][$name] = $this->commands[$name];
		} else {
			$this->settings['commands'][$name] = array_merge($this->commands[$name], $this->settings['commands'][$name]);
		}
	}

/**
 * initialize method
 *
 * @return void
 * @access public
 */
	public function initialize() {
		if (!empty($this->params['q']) || !empty($this->params['quiet']) || !empty($this->params['-quiet'])) {
			$this->settings['quiet'] = true;
		}
		if (!empty($this->params['output'])) {
			$this->settings['toFile'] = $this->params['output'];
		}
		if (!empty($this->params['o'])) {
			$this->params['output'] = $this->settings['toFile'] = $this->params['o'];
			unset($this->params['o']);
		}

		if (!empty($this->params['input'])) {
			$this->settings['file'] = $this->params['input'];
		}
		if (!empty($this->params['file'])) {
			$this->settings['file'] = $this->params['file'];
		}
		if (!empty($this->params['f'])) {
			$this->params['file'] = $this->settings['file'] = $this->params['f'];
			unset($this->params['f']);
		}
		if (!empty($this->params['v'])) {
			$this->params['debug'] = true;
			unset($this->params['v']);
		}

		if (!empty($this->params['models'])) {
			$models = explode(',', $this->params['models']);
			$connecitons = $tables = array();
			foreach($models as $model) {
				$Model = ClassRegistry::init($model);
				$connections[$Model->useDbConfig] =& ConnectionManager::getDataSource($Model->useDbConfig);
				$tables[] = $connections[$Model->useDbConfig]->fullTableName($Model, false);
			}
			if (count($connections) !== 1) {
				return trigger_error('MiDbShell:: mixed connections are not supported when dumping tables');
			}
			$this->settings['connection'] = key($connections);
			$this->settings['table'] = implode(' ', array_unique($tables));
		}

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
		$this->settings = array_merge($this->settings, $this->params);
		if (empty($this->commands['mysqli'])) {
			$this->commands['mysqli'] = $this->commands['mysql'];
		}
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
			$settings['toFile'] = $this->_backupName(CONFIGS . 'schema' . DS . 'backups' . DS . $this->settings['connection']);
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
			$settings['toFile'] = CONFIGS . 'schema' . DS . $this->settings['connection'];
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
				$file = CONFIGS . 'schema' . DS . $this->settings['connection'];
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
				$file = CONFIGS . 'schema' . DS . $this->settings['connection'];
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
		$command = str_replace(' :table < :file', '', $this->settings['commands'][$name]['import']);
		$import = $this->_command($command, $toDb->config, $name, $this->settings);

		$command = "$dump | $import";
		if (empty($this->settings['quiet'])) {
			$this->out("Copying tables from $from to $to");
		}
		return $this->_out($command, $this->settings);
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
			$file = CONFIGS . 'schema' . DS . $this->settings['connection'] . $file . '.sql';
		}
		if (empty($this->params['force'])) {
			$this->out(file_get_contents($file, null, null, 0, 1000) . '...');
			$continue = strtoupper($this->in("Import $file into {$this->settings['connection']}?", array('Y', 'N')));
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
			$from = CONFIGS . 'schema' . DS . $this->settings['connection'] . $from . '.sql';
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
		$db =& ConnectionManager::getDataSource($settings['connection']);
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
 * welcome method
 *
 * @return void
 * @access protected
 */
	public function _welcome() {
		if ($this->settings['quiet']) {
			return;
		}
		parent::_welcome();
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
		if (!empty($settings['debug'])) {
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
 * exec method
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
}