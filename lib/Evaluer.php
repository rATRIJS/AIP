<?php
namespace AIP\lib;

class Evaluer {
	public static $path = array();
	public static $storage = array();
	
	protected static $sandbox_vars = array();
	
	public static function execute(Statement $statement) {
		$result = new Result;
		
		if($statement->in_block()) {
			$result->message = 'Not yet finished';
			
			return $result;
		}
		
		$result->php = $statement->to_php();
		
		ob_start();
		$result->return = self::sandboxed_eval($result->php);
		$result->output = ob_get_clean();
		
		return $result;
	}
	
	public static function sandbox_vars($vars = null, $merge = true) {
		$path = self::pathenize();
		
		if(is_array($vars))
			self::$sandbox_vars[$path] = $merge ? array_merge(self::$sandbox_vars[$path], $vars) : $vars;
			
		return self::$sandbox_vars[$path];
	}
	
	public static function init_storage($key, $value) {
		if(!isset(self::$storage[$key]))
			self::$storage[$key] = $value;
	}
	
	public static function pathenize() {
		return '/' . implode('/', self::$path);
	}
	
	protected static function sandboxed_eval($__aip_php) {
		$__aip_path = self::pathenize();
		
		if(isset(self::$sandbox_vars[$__aip_path]))
			extract(self::$sandbox_vars[$__aip_path], EXTR_SKIP);
		
		$__aip_return = eval($__aip_php);
		
		self::$sandbox_vars[$__aip_path] = get_defined_vars();
		unset(
			self::$sandbox_vars[$__aip_path]['__aip_path'],
			self::$sandbox_vars[$__aip_path]['__aip_php'],
			self::$sandbox_vars[$__aip_path]['__aip_return']
		);
		
		return $__aip_return;
	}
}