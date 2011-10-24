<?php
namespace AIP\lib\clnt\cmnctr;

use \AIP\excptns\lib\clnt\cmnctr as E;
use \AIP\lib as L;

class Communicator {
	const MODE_SERVER_CLIENT = 1;
	const MODE_SIMPLE_REPL = 2;
	
	protected static $_i;
	
	protected $_communicator;
	protected $_result;
	protected $_statement;
	
	public static function i() {
		if(!isset(self::$_i)) self::$_i = new self;
		
		return self::$_i;
	}
	
	protected function __construct() {
		$this->_setup_active_communicator();
	}
	
	public function send($input) {
		$this->_communicator->send($input);
		$this->_statement = $this->_communicator->get_statement();
		$this->_result = $this->_communicator->get_result();
		
		return $this;
	}
	
	public function retrieve() {
		if(!isset($this->_result))
			throw new E\NoResponseException("No response available.");
		
		return $this->_result;
	}
	
	public function is_interrupted() {
		if(!isset($this->_statement))
			throw new E\NoResponseException("No statement available because no response has been received.");
		
		return $this->_statement->interrupted();
	}
	
	public function get_path() {
		return $this->_communicator->get_path();
	}
	
	protected function _setup_active_communicator() {
		$available_communicators = L\Config::get(L\Config::OPTION_COMMUNICATORS);
		
		foreach($available_communicators as $communicator) {
			if($communicator::available()) {
				$this->_communicator = new $communicator;
				
				break;
			}
		}
		
		if(!$this->_communicator instanceof ICommunicator)
			throw new E\InvalidCommunicatorException("Communicator must implement ICommunicator class.");
	}
}