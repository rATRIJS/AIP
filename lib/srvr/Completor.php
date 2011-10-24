<?php
namespace AIP\lib\srvr;

class Completor {
	public static function init() {
		readline_completion_function(array('\AIP\lib\Completor', 'complete'));
	}
	
	public static function complete($keyword) {
		$keyword = self::get_real_keyword($keyword);
		
		if(substr($keyword, 0, 1) === '$')
			return self::complete_var($keyword);
		else
			return self::complete_obj($keyword);
			
		return array();
	}
	
	public static function complete_var($var) {
		$sandbox_vars = Evaluer::sandbox_vars();
		
		return self::match(substr($var, 1), array_keys($sandbox_vars));
	}
	
	public static function complete_obj($obj) {
		$objects = get_defined_functions();
		
		$objects = array_merge(
			$objects['internal'],
			$objects['user'],
			get_declared_classes()
		);
		
		return self::match($obj, $objects);
	}
	
	protected static function match($needle, $haystack) {
		$needle_len = strlen($needle);
		
		$matches = array();
		foreach($haystack as $candidate)
			if(substr($candidate, 0, $needle_len) === $needle)
				$matches[] = $candidate;
				
		return $matches;
	}
	
	protected static function get_real_keyword($keyword) {
		$info = readline_info();
		
		if(!is_array($info) or !isset($info['pending_input']))
			return $keyword;
		
		return substr($info['line_buffer'], 0, $info['end']);
	}
}