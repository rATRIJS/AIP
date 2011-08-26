<?php
namespace AIP\lib\lang\fns;

class AIPLang_Function_LS extends AIPLang_Function {
	public static function parsable($line) {
		return substr($line, 0, 2) == 'ls' and (strlen($line) === 2 or substr($line, 0, 3) === 'ls ');
	}
	
	public static function parse($line) {
		$line = explode(' ', $line, 2);
		
		if(!isset($line[1]))
			$line[1] = '.';
			
		$args = explode(' ', $line[1]);
		$target = '.';
		if(substr($args[count($args) - 1], 0, 1) !== '-')
			$target = array_pop($args);
			
		if(!in_array(substr($target, 0, 1), array('\'', '"', '$')))
			$target = "'{$target}'";
		
		return "\AIP\lib\Evaluer::ls({$target})";
	}
}