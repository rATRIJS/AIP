<?php
namespace AIP\lib;

use AIP\excptns\lib\input as E;

class Input {
	protected static $i;
	
	protected $_reader;
	
	public static function i() {
		if(!isset(self::$i))
			self::$i = new self;

		return self::$i;
	}
	
	public static function read($path = '/') {
		return self::i()->read_line($path);
	}
	
	protected function __construct() {
		$readers = Config::get(Config::OPTION_INPUT_READERS);
		
		foreach($readers as $reader) {			
			if(!in_array('AIP\\lib\\rdrs\\Reader', class_parents($reader)))
				throw new E\AIPInput_InvalidReaderException("'{$reader}' must extend the \\AIP\\lib\\rdrs\\Reader class.");
				
			if($reader::supported()) {
				$this->_reader = new $reader;
				
				break;
			}
		}
	}
	
	public function read_line($path = '/') {
		return $this->_reader->read($path);
	}
}