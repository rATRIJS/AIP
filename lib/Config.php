<?php
namespace AIP\lib;

use \AIP\excptns\lib\config as E;

class Config {
	const MODE_REPLACE = 'replace';
	const MODE_MERGE = 'merge';
	const MODE_EXCLUDE = 'exclude';
	
	const OPTION_AIP_LANG_CONSTRUCTS = 'aip_lang_constructs';
	const OPTION_BEFORE_LOOP_EXEC = 'before_loop_exec';
	
	protected static $i;
	
	protected $_config;
	protected $_mode_map;
	
	public static function i() {
		if(!isset(self::$i)) self::$i = new self;
		
		return self::$i;
	}
	
	public static function get($key) {
		return self::i()->get_option($key);
	}
	
	protected function __construct() {
		$config_file = _AIP_PATH . '/config.php';
		$this->_config = file_exists($config_file) ? require $config_file : array();
		
		$this->_mode_map = array(
			self::MODE_REPLACE => function($defaults, $args) {
				return $args;
			},
			self::MODE_MERGE => function($defaults, $args) {
				return array_unique(array_merge($defaults, $args));
			},
			self::MODE_EXCLUDE => function($defaults, $args) {
				foreach($defaults as $k => $default)
					if(in_array($default, $args)) unset($defaults[$k]);

				return $defaults;
			}
		);
		
		$this->_validate();
	}
	
	protected function get_option($key) {
		if(!isset($this->_config[$key]))
			throw new E\AIPConfig_NotFoundException("Option with key '{$key}' was not found.");
			
		return $this->_config[$key];
	}
	
	protected function _validate() {
		if(!is_array($this->_config))
			throw new E\AIPConfig_FileException('Configuration file must return an array.');
		
		$this->_init_before_loop_exec();
		$this->_init_aip_lang_constructs();
	}
	
	protected function _init_before_loop_exec() {
		$this->_defaultize_array_option(
			self::OPTION_BEFORE_LOOP_EXEC,
			self::MODE_MERGE,
			'exec',
			array()
		);
	}
	
	protected function _init_aip_lang_constructs() {
		$this->_defaultize_array_option(
			self::OPTION_AIP_LANG_CONSTRUCTS,
			self::MODE_MERGE,
			'constructs',
			array(
				'\AIP\lib\lang\fns\AIPLang_Function_CD',
				'\AIP\lib\lang\fns\AIPLang_Function_LS',
				'\AIP\lib\lang\fns\AIPLang_Function_SHOW_SOURCE',
				'\AIP\lib\lang\fns\AIPLang_Function_THIS'
			)
		);
	}
	
	protected function _defaultize_array_option($option, $default_mode, $key, $defaults) {
		if(empty($this->_config[$option])) $this->_config[$option] = array(
			'mode' => $default_mode,
			$key => array()
		);
		
		$o =& $this->_config[$option];
		
		if(!is_array($o))
			throw new E\AIPConfig_OptionException("'{$option}' must be specified as an array.");
			
		$mode = isset($o['mode']) ? $o['mode'] : $default_mode;
		$args = array();
		if(isset($o[$key])) $args = $o[$key];
		elseif(!isset($o[$key]) and !isset($o['mode'])) $args = $o;
		
		$o = $this->_mode_action($mode, $defaults, $args);
	}
	
	protected function _mode_action($mode, $defaults, $args) {
		if(!isset($this->_mode_map[$mode]))
			throw new E\AIPConfig_ModeException("Specified mode '{$mode}' doesn't exist.");
		
		return call_user_func($this->_mode_map[$mode], $defaults, $args);
	}
}