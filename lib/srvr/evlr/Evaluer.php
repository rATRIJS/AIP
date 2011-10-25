<?php
namespace AIP\lib\srvr\evlr;

use \AIP\excptns\lib as E;
use \AIP\lib\srvr\prsr as P;

class Evaluer {
	const SOURCE_RETURN = 'return';
	const SOURCE_OUTPUT = 'output';
	
	public static $path = array();
	public static $reflections = array();
	public static $instances = array();
	
	protected static $sandbox_vars = array();
	protected static $internalize_result = false;
	protected static $last_was_unfinished = false;
	
	public static function execute(P\Statement $statement) {
		$result = new Result;
		
		if($statement->in_block()) {
			$result->message = 'Not yet finished';
			$result->return = hlprs\NotReturnable::i();
			
			self::$last_was_unfinished = true;
			
			return $result;
		}
		
		$result->php = $statement->to_php();
		
		\AIP\lib\srvr\History::i()->add($statement->to_aip());
		if(self::$last_was_unfinished) {
			self::$last_was_unfinished = false;
			
			\AIP\lib\srvr\History::i()->confirm($result->php);
		}
		else {
			\AIP\lib\srvr\History::i()->confirm();
		}
		
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
	
	public static function pathenize() {
		return '/' . implode('/', self::$path);
	}
	
	public static function reflection($reflection = false) {
		$current_path = self::pathenize();
		
		if($reflection !== false) {
			if(null === $reflection) unset(self::$reflections[$current_path]);
			else self::$reflections[$current_path] = $reflection;
		}
		
		return isset(self::$reflections[$current_path]) ? self::$reflections[$current_path] : false;
	}
	
	public static function instance($instance = false) {
		$current_path = self::pathenize();
		
		if($instance !== false) {
			if(null === $instance) unset(self::$instances[$current_path]);
			else self::$instances[$current_path] = $instance;
		}
		
		return isset(self::$instances[$current_path]) ? self::$instances[$current_path] : false;
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