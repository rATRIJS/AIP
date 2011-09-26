<?php
namespace AIP\lib\lang\fns;

abstract class AIPLang_Function extends \AIP\lib\lang\AIPLang_Construct {
	protected static function quotenize($target) {
		if(!in_array(substr($target, 0, 1), array('$', '\'', '"')) and substr($target, -1) !== ')')
			$target = "'{$target}'";
			
		return $target;
	}
	
	protected static function var_quotenize($target) {
		if(!in_array(substr($target, 0, 1), array('$', '\'', '"')))
			$target = "'{$target}'";
			
		return $target;
	}
	
	protected static function extract_function($line, $prefix) {
		$start = self::_calculate_start($line, $prefix);
		if(substr($line, $start, 1) !== '(')
			throw new \AIP\excptns\lib\lang\fns\AIPLang_Function_NotValidFunctionException("Given prefix isn't valid function.");
		
		$current = $start;
		$depth = 0;
		$function = '';
		while(false !== ($c = substr($line, $current, 1))) {
			if($c === '(') $depth++;
			if($c === ')') $depth--;
			
			$function .= $c;
			
			if($depth === 0) break;
			
			$current++;
		}
		
		if($depth !== 0) die('FN::23');
		
		return $prefix . $function;
	}
	
	protected static function extract_name($line, $prefix) {
		$start = self::_calculate_start($line, $prefix);
		
		$current = $start;
		$name = '';
		while(false !== ($c = substr($line, $current, 1))) {
			if(!preg_match('#([a-zA-Z_\$])#iU', $c)) break;
			
			$name .= $c;
			$current++;
		}
		
		return $prefix . $name;
	}
	
	protected static function extract_args($function) {
		$start = strpos($function, '(');
		
		if($start === false)
			throw new \AIP\excptns\lib\lang\fns\AIPLang_Function_NotValidFunctionException("Given prefix isn't valid function.");
		
		return substr($function, $start + 1, -1);
	}
	
	protected static function extract_setter_value($line, $prefix) {
		$start = self::_calculate_start($line, $prefix);
		
		$current = $start;
		while(false !== ($c = substr($line, $current, 1))) {
			if(!in_array($c, array(' ', '='))) break;
			$current++;
		}
		
		$value = $current === $start ? '' : trim(substr($line, $current));
		
		if(empty($value))
			throw new \AIP\excptns\lib\lang\fns\AIPLang_Function_NotValidSetterException("Given prefix isn't valid setter.");
			
		return $value;
	}
	
	protected static function extract_name_function_arguments(&$line, $prefix) {
		$name = self::extract_name($line, $prefix);
		try {
			$function = self::extract_function($line, $name);
			$arguments = self::extract_args($function);
		}
		catch(\AIP\excptns\lib\lang\fns\AIPLang_Function_NotValidFunctionException $e) {
			$function = false;
			$arguments = false;
		}
			
		if($function === false) {
			try {
				$arguments = self::extract_setter_value($line, $name);
				$line = $name;
			}
			catch(\AIP\excptns\lib\lang\fns\AIPLang_Function_NotValidSetterException $e) {
				$arguments = false;
			}
		}
		
		return compact('name', 'function', 'arguments');
	}
	
	protected static function _calculate_start($line, $prefix) {
		$start = strpos($line, $prefix);
		if($start === false) die('FN::34');
		
		return $start + strlen($prefix);
	}
}