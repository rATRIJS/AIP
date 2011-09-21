<?php
namespace AIP\lib;

use \AIP\excptns\lib as E;

class Evaluer {
	const SOURCE_RETURN = 'return';
	const SOURCE_OUTPUT = 'output';
	
	public static $path = array();
	public static $storage = array();
	
	protected static $sandbox_vars = array();
	protected static $internalize_result = false;
	
	public static function execute(Statement $statement) {
		$result = new Result;
		
		if($statement->in_block()) {
			$result->message = 'Not yet finished';
			$result->return = hlprs\NotReturnable::i();
			
			return $result;
		}
		
		$result->php = $statement->to_php();
		
		ob_start();
		$result->return = self::sandboxed_eval($result->php);
		$result->output = ob_get_clean();
		
		if(self::$internalize_result !== false) {
			$result->internal = array(
				'title' => self::$internalize_result['title'],
				'body' => self::$internalize_result['source'] === self::SOURCE_RETURN ? $result->return : $result->output
			);
		}
			
		self::make_internal_from(false);
		
		return $result;
	}
	
	public static function make_internal_from($source = false, $title = false) {
		if($source !== false)
			self::_validate_source($source);
		
		self::$internalize_result = $source === false ? false : compact('source', 'title');
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
	
	protected static function _validate_source($source) {
		if(!in_array($source, array(self::SOURCE_RETURN, self::SOURCE_OUTPUT)))
			throw new E\AIPEvaluer_InvalidSourceException("Given source `{$source}` isn't valid.");
	}
}