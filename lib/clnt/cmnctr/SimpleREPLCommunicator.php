<?php
namespace AIP\lib\clnt\cmnctr;

use \AIP\lib\srvr\prsr as P;
use \AIP\lib\srvr\evlr as Ev;

class SimpleREPLCommunicator implements ICommunicator {
	protected $_statement;
	protected $_result;
	
	public static function available() {
		return true;
	}
	
	public function send($input) {
		$this->_statement = P\Parser::i()->parse($input);
		$this->_result = Ev\Evaluer::execute($this->_statement);
		
		return $this;
	}
	
	public function get_path() {
		return Ev\Evaluer::pathenize();
	}
	
	public function get_statement() {
		return $this->_statement;
	}
	
	public function get_result() {
		return $this->_result;
	}
}