<?php
namespace AIP\lib\lang;

abstract class AIPLang_Construct {
	abstract public static function parsable($line, $statement);
	abstract public static function parse($line, $statement);
	
	protected static function get_current_reflection() {
		$current_path = static::get_current_path();
		$current_reflection = isset(\AIP\lib\Evaluer::$storage['reflections'][$current_path]) ?
			\AIP\lib\Evaluer::$storage['reflections'][$current_path] : false;
			
		return $current_reflection;
	}
	
	protected static function get_current_path() {
		return \AIP\lib\Evaluer::pathenize();
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
				
				$sandbox_vars = \AIP\lib\Evaluer::sandbox_vars();
				if(!isset($sandbox_vars[$var_name])) die('CONSTRUCT::55');
				
				$var = $sandbox_vars[$var_name];
				if(!is_object($var)) die('CONSTRUCT::58');
				
				$reflection = new \AIP\lib\Reflectionizer(array($var, $target[1]));
				return $reflection->reflectionize();
			}
			else {
				$reflection = new \AIP\lib\Reflectionizer($target);
				return $reflection->reflectionize();
			}
		}
		elseif(substr($target, 0, 1) === '$') {
			$var_name = substr($target, 1);
			
			$sandbox_vars = \AIP\lib\Evaluer::sandbox_vars();
			if(!isset($sandbox_vars[$var_name])) die('CONSTRUCT::67');
			
			$reflection = new \AIP\lib\Reflectionizer($sandbox_vars[$var_name]);
			return $reflection->reflectionize();
		}
		else {
			$reflection = new \AIP\lib\Reflectionizer($target);
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