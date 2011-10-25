<?php
namespace AIP\lib\srvr\lang;

use \AIP\lib\srvr as S;

abstract class AIPLang_Construct {
	abstract public static function parsable($line, $statement);
	abstract public static function parse($line, $statement);
	
	protected static function _get_namespaced_self() {
		return '\\' . get_called_class();
	}
	
	protected static function error_before_eval($title, $message) {
		S\evlr\Evaluer::make_internal_from(
			S\evlr\Evaluer::SOURCE_OUTPUT,
			$title
		);
		
		return 'echo \'' . addslashes($message) . '\'';
	}
	
	protected static function error_in_eval($title, $message) {
		S\evlr\Evaluer::make_internal_from(
			S\evlr\Evaluer::SOURCE_OUTPUT,
			$title
		);
		
		echo $message;
		
		return \AIP\lib\hlprs\NotReturnable::i();
	}
	
	protected static function reflection_target_to_reflection($target) {
		$current_reflection = S\evlr\Evaluer::reflection();
		
		if(is_array($target)) {
			if($target[0] === '$this') {
				$method = substr($target[1], 0, -2);
				
				if(!$current_reflection instanceof \ReflectionClass) die('CONSTRUCT::44');
				if(!$current_reflection->hasMethod($method)) die('CONSTRUCT::45');
				
				return $current_reflection->getMethod($method);
			}
			elseif(substr($target[0], 0, 1) === '$') {
				$var_name = substr($target[0], 1);
				
				$sandbox_vars = S\evlr\Evaluer::sandbox_vars();
				if(!isset($sandbox_vars[$var_name])) die('CONSTRUCT::55');
				
				$var = $sandbox_vars[$var_name];
				if(!is_object($var)) die('CONSTRUCT::58');
				
				$reflection = new S\Reflectionizer(array($var, $target[1]));
				return $reflection->reflectionize();
			}
			else {
				$reflection = new S\Reflectionizer($target);
				return $reflection->reflectionize();
			}
		}
		elseif(substr($target, 0, 1) === '$') {
			$var_name = substr($target, 1);
			
			$sandbox_vars = S\evlr\Evaluer::sandbox_vars();
			if(!isset($sandbox_vars[$var_name])) die('CONSTRUCT::67');
			
			$reflection = new S\Reflectionizer($sandbox_vars[$var_name]);
			return $reflection->reflectionize();
		}
		else {
			$reflection = new S\Reflectionizer($target);
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