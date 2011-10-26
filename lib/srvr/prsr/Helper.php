<?php
namespace AIP\lib\srvr\prsr;

use \AIP\excptns\lib\srvr\prsr as E;

class Helper {
	public static function starts_block($line) {
		return in_array(substr($line, -1), array('{', ':'));
	}
	
	public static function ends_block($line) {
		return substr($line, -1) === '}';
	}
	
	public function semicolonize($line) {
		if(substr($line, -1) !== ';')
			$line .= ';';
		
		return $line;
	}
	
	public static function quotenize($target) {
		if(!in_array(substr($target, 0, 1), array('$', '\'', '"')) and substr($target, -1) !== ')')
			$target = "'{$target}'";
			
		return $target;
	}
	
	public static function var_quotenize($target) {
		if(!in_array(substr($target, 0, 1), array('$', '\'', '"')))
			$target = "'{$target}'";
			
		return $target;
	}
	
	public static function extract_function($line, $prefix) {
		$start = self::_calculate_start($line, $prefix);
		if(substr($line, $start, 1) !== '(')
			throw new E\NotValidFunctionException("Given prefix isn't valid function.");
		
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
		
		if($depth !== 0) die('HLPR::23');
		
		return $prefix . $function;
	}
	
	public static function extract_name($line, $prefix) {
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
	
	public static function extract_args($function) {
		$start = strpos($function, '(');
		
		if($start === false)
			throw new E\NotValidFunctionException("Given prefix isn't valid function.");
		
		return substr($function, $start + 1, -1);
	}
	
	public static function extract_setter_value($line, $prefix) {
		$start = self::_calculate_start($line, $prefix);
		
		$current = $start;
		while(false !== ($c = substr($line, $current, 1))) {
			if(!in_array($c, array(' ', '='))) break;
			$current++;
		}
		
		$value = $current === $start ? '' : trim(substr($line, $current));
		
		if(empty($value))
			throw new E\NotValidSetterException("Given prefix isn't valid setter.");
			
		return $value;
	}
	
	public static function extract_name_function_arguments(&$line, $prefix) {
		$name = self::extract_name($line, $prefix);
		try {
			$function = self::extract_function($line, $name);
			$arguments = self::extract_args($function);
		}
		catch(E\NotValidFunctionException $e) {
			$function = false;
			$arguments = false;
		}
			
		if($function === false) {
			try {
				$arguments = self::extract_setter_value($line, $name);
				$line = $name;
			}
			catch(E\NotValidSetterException $e) {
				$arguments = false;
			}
		}
		
		return compact('name', 'function', 'arguments');
	}
	
	public static function interrupts($line) {
		$valid_interrupts = array(
			'die;',
			'die();',
			'exit;',
			'exit();'
		);
		
		return in_array($line, $valid_interrupts);
	}
	
	public static function aip_die() {
		return '';
	}
	
	public static function sanitize($line) {
		$line = trim($line);
		
		return $line;
	}
	
	protected static function _calculate_start($line, $prefix) {
		$start = strpos($line, $prefix);
		if($start === false) die('HLPR::34');
		
		return $start + strlen($prefix);
	}
}