<?php
namespace AIP\lib\srvr\prsr;

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
}