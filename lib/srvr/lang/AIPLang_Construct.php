<?php
namespace AIP\lib\srvr\lang;

use \AIP\lib as L;

abstract class AIPLang_Construct {
	abstract public static function parsable($line, $statement);
	abstract public static function parse($line, $statement);
	
	protected static function _get_namespaced_self() {
		return '\\' . get_called_class();
	}
	
	protected static function get_current_reflection() {
		$current_path = static::get_current_path();
		
		return (isset(L\Evaluer::$storage['reflections']) and isset(L\Evaluer::$storage['reflections'][$current_path])) ?
			L\Evaluer::$storage['reflections'][$current_path] : false;
	}
	
	protected static function get_current_instance() {
		$current_path = static::get_current_path();
		
		return (isset(L\Evaluer::$storage['instances']) and isset(L\Evaluer::$storage['instances'][$current_path])) ?
			L\Evaluer::$storage['instances'][$current_path] : false;
	}
	
	protected static function get_current_path() {
		return L\Evaluer::pathenize();
	}
	
	protected static function error_before_eval($title, $message) {
		L\Evaluer::make_internal_from(
			L\Evaluer::SOURCE_OUTPUT,
			$title
		);
		
		return 'echo \'' . addslashes($message) . '\'';
	}
	
	protected static function error_in_eval($title, $message) {
		L\Evaluer::make_internal_from(
			L\Evaluer::SOURCE_OUTPUT,
			$title
		);
		
		echo $message;
		
		return L\hlprs\NotReturnable::i();
	}
	
	protected static function reflection_target_to_reflection($target) {
		$current_reflection = static::get_current_reflection();
		
		if(is_array($target)) {
			if($target[0] === '$this') {
				$method = substr($target[1], 0, -2);
				
				if(!$current_reflection instanceof \ReflectionClass) die('CONSTRUCT::44');
				if(!$current_reflection->hasMethod($method)) die('CONSTRUCT::45');
				
				return $current_reflection->getMethod($method);
			}
			elseif(substr($target[0], 0, 1) === '$') {
				$var_name = substr($target[0], 1);
				
				$sandbox_vars = L\Evaluer::sandbox_vars();
				if(!isset($sandbox_vars[$var_name])) die('CONSTRUCT::55');
				
				$var = $sandbox_vars[$var_name];
				if(!is_object($var)) die('CONSTRUCT::58');
				
				$reflection = new L\Reflectionizer(array($var, $target[1]));
				return $reflection->reflectionize();
			}
			else {
				$reflection = new L\Reflectionizer($target);
				return $reflection->reflectionize();
			}
		}
		elseif(substr($target, 0, 1) === '$') {
			$var_name = substr($target, 1);
			
			$sandbox_vars = L\Evaluer::sandbox_vars();
			if(!isset($sandbox_vars[$var_name])) die('CONSTRUCT::67');
			
			$reflection = new L\Reflectionizer($sandbox_vars[$var_name]);
			return $reflection->reflectionize();
		}
		else {
			$reflection = new L\Reflectionizer($target);
			return $reflection->reflectionize();
		}
	}
	
	protected function merge_args($defaults, $args) {
		foreach($args as $k => $v)
			if(!isset($defaults[$k]))
				unset($args[$k]);
				
		return array_merge($defaults, $args);
	}
}