<?php
namespace AIP\lib\clnt\rdrs;

class Readline extends Reader {
	protected $_last_input;
	
	public static function supported() {
		return function_exists('readline');
	}
	
	public function __construct() {}
	
	public function read($path) {
		$this->_historize(\AIP\lib\clnt\cmnctr\Communicator::i()->get_last_history());
		
		$line = readline($this->_get_input_question($path));
		
		return $line;
	}
	
	protected function _historize($line) {
		if($this->_last_input !== $line)
			readline_add_history($line);
			
		$this->_last_input = $line;
	}
}