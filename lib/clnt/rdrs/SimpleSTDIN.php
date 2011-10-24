<?php
namespace AIP\lib\clnt\rdrs;

class SimpleSTDIN extends Reader {
	public static function supported() {
		return true;
	}
	
	public function __construct() {}
	
	public function read($path) {
		echo $this->_get_input_question($path);
		
		$h = fopen('php://stdin', 'r');
		
		$line = trim(fgets($h));
		
		fclose($h);
		
		return $line;
	}
}