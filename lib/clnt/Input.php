<?php
namespace AIP\lib\clnt;

use \AIP\excptns\lib\clnt\input as E;
use \AIP\lib as L;

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
		$this->_setup_reader();
	}
	
	public function read_line($path = '/') {
		$line = $this->_reader->read($path);
		
		return $line;
	}
	
	protected function _setup_reader() {
		$readers = L\Config::get(L\Config::OPTION_INPUT_READERS);
		
		foreach($readers as $reader) {			
			if(!in_array('AIP\\lib\\clnt\\rdrs\\Reader', class_parents($reader)))
				throw new E\AIPInput_InvalidReaderException("'{$reader}' must extend the \\AIP\\lib\\clnt\\rdrs\\Reader class.");
				
			if($reader::supported()) {
				$this->_reader = new $reader;
				
				break;
			}
		}
	}
}