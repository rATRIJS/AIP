<?php
namespace AIP\lib;

class Output {
	public static function write(Result $result) {
		self::raw_write($result->render());
	}
	
	public static function raw_write($s) {
		echo $s . "\n";
	}
}