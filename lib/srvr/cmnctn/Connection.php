<?php
namespace AIP\lib\srvr\cmnctn;

class Connection {
	protected $_socket;
	
	public function __construct($socket) {
		$this->_socket = $socket;
	}
	
	public function &socket($socket = null) {
		if(!isset($socket)) return $this->_socket;
		
		$this->_socket = $socket;
		
		return $this;
	}
}