<?php
namespace AIP\lib\srvr\prsr;

use \AIP\lib as L;

class Aipenizer {
	protected static $_i;
	
	protected $constructs;
	
	public static function i() {
		if(!isset(self::$_i)) self::$_i = new self;
		
		return self::$_i;
	}
	
	protected function __construct() {
		$this->constructs = L\Config::get(L\Config::OPTION_AIP_LANG_CONSTRUCTS);
	}
	
	public function run($line, Statement $statement) {
		foreach($this->constructs as $construct)
			if($construct::parsable($line, $statement))
				$line = $construct::parse($line, $statement);
		
		return $line;
	}
}