<?php
namespace AIP\lib;

class Input {
	protected static $last_input;
	
	public static function read($path = '/') {
		$line = readline("{$path}$: ");
		
		self::historize($line);
		
		return $line;
	}
	
	protected static function historize($line) {
		if(self::$last_input !== $line)
			readline_add_history($line);
			
		self::$last_input = $line;
	}
}