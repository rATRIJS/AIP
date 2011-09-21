<?php
namespace AIP\lib\lang\fns;

class AIPLang_Function_AUTO_RETURNER extends AIPLang_Function {
	protected static $_invalid_starts = array(
		'foreach',
		'for',
		'while',
		'switch',
		'return',
		'echo',
		'print',
		'}'
	);
	
	public static function parsable($line, $statement) {
		return !$statement->in_block() and self::_is_valid_start($line);
	}
	
	public static function parse($line, $statement) {
		return "return {$line}";
	}
	
	protected static function _is_valid_start($line) {
		foreach(self::$_invalid_starts as $k)
			if(substr($line, 0, strlen($k)) === $k) return false;
			
		return true;
	}
}