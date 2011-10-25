<?php
namespace AIP\lib\srvr;

use \AIP\excptns\lib\srvr\hstry as E;

class History {
	protected static $_i;
	
	protected $_history;
	protected $_max_size;
	protected $_last_index;
	protected $_unconfirmed;
	
	public static function i() {
		if(!isset(self::$_i))
			self::$_i = new self;
		
		return self::$_i;
	}
	
	protected function __construct() {
		$this->_history = array();
		$this->_max_size = \AIP\lib\Config::get(\AIP\lib\Config::OPTION_HISTORY_SIZE);
		$this->_last_index = 0;
		$this->_unconformed_history = false;
	}
	
	public function add($line) {
		$this->_unconfirmed = $line;
		
		return $this;
	}
	
	public function confirm($override = false) {
		$i = $this->_last_index + 1;
		
		$this->_history[$i] = (false === $override) ? $this->_unconfirmed : $override;
		
		if($i >= $this->_max_size) $i = 0;
		
		$this->_last_index = $i;
		$this->_unconfirmed = false;
		
		return $this;
	}
	
	public function get($length = 1, $start = 1) {
		$start -= 1;
		
		if(($start + $length) > $this->_max_size)
			throw new E\SizeException("Requested history is off bounds. Maximum size is: {$this->_max_size}.");
		
		$current = $this->_last_index;
		for($start; $start !== 0; $start--)
			if(--$current === 0) $current = $this->_max_size;

		$history = array();
		for($i = $length; $i !== 0; $i--) {
			if(!isset($this->_history[$current])) break;
			
			$history[$current] = $this->_history[$current];
			
			$current--;
		}
		
		return $history;
	}
	
	public function get_by_id($id) {
		if(!isset($this->_history[$id]))
			throw new E\InvalidIDException("Requested history ID #{$id} doesn't exist.");
		
		return $this->_history[$id];
	}
	
	public function is_confirmed() {
		return false === $this->_unconfirmed;
	}
}