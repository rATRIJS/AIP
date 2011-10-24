<?php
namespace AIP\lib\clnt;

class Output {
	public static function write(\AIP\lib\srvr\evlr\Result $result) {
		self::raw_write($result->render());
	}
	
	public static function raw_write($s) {
		echo $s . "\n";
	}
}