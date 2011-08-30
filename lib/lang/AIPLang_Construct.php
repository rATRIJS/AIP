<?php
namespace AIP\lib\lang;

abstract class AIPLang_Construct {
	abstract public static function parsable($line);
	abstract public static function parse($line);
	
	protected static function get_current_reflection() {
		$current_path = static::get_current_path();
		$current_reflection = isset(\AIP\lib\Evaluer::$storage['reflections'][$current_path]) ?
			\AIP\lib\Evaluer::$storage['reflections'][$current_path] : false;
			
		return $current_reflection;
	}
	
	protected static function get_current_path() {
		return \AIP\lib\Evaluer::pathenize();
	}
	
	protected function merge_args($defaults, $args) {
		foreach($args as $k => $v)
			if(!isset($defaults[$k]))
				unset($args[$k]);
				
		return array_merge($defaults, $args);
	}
}