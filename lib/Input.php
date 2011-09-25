<?php
namespace AIP\lib;

use AIP\excptns\lib\input as E;

class Input {
	protected static $i;
	
	protected $_reader;
	protected $_history;
	protected $_max_history_size;
	protected $_last_history_index;
	protected $_unconfirmed_history;
	
	public static function i() {
		if(!isset(self::$i))
			self::$i = new self;

		return self::$i;
	}
	
	public static function read($path = '/') {
		return self::i()->read_line($path);
	}
	
	public static function confirm($override = false) {
		return self::i()->confirm_history($override);
	}
	
	public static function history($length = 1, $start = 1) {
		return self::i()->get_history($length, $start);
	}
	
	public static function history_id($id) {
		return self::i()->get_history_by_id($id);
	}
	
	protected function __construct() {
		$this->_setup_reader();
		
		$this->_history = array();
		$this->_max_history_size = Config::get(Config::OPTION_HISTORY_SIZE);
		$this->_last_history_index = 0;
		$this->_unconfirmed_history = true;
	}
	
	public function read_line($path = '/') {
		$line = $this->_reader->read($path);
		
		$this->_unconfirmed_history = $line;
		
		return $line;
	}
	
	public function get_history($length = 1, $start = 1) {
		$start -= 1;
		
		if(($start + $length) > $this->_max_history_size)
			throw new E\AIPInput_HistorySizeException("Requested history is off bounds. Maximum size is: {$this->_max_history_size}.");
		
		$current = $this->_last_history_index;
		for($start; $start !== 0; $start--)
			if(--$current === 0) $current = $this->_max_history_size;

		$history = array();
		for($i = $length; $i !== 0; $i--) {
			if(!isset($this->_history[$current])) break;
			
			$history[$current] = $this->_history[$current];
			
			$current--;
		}
		
		return $history;
	}
	
	public function get_history_by_id($id) {
		if(!isset($this->_history[$id]))
			throw new E\AIPInput_InvalidHistoryIDException("Requested history ID #{$id} doesn't exist.");
		
		return $this->_history[$id];
	}
	
	public function confirm_history($override = false) {
		$i = $this->_last_history_index + 1;
		
		$this->_history[$i] = ($override === false) ?
			$this->_unconfirmed_history : $override;
		
		if($i >= $this->_max_history_size) $i = 0;
		
		$this->_last_history_index = $i;
		$this->_unconfirmed_history = false;
		
		return $this;
	}
	
	public function is_history_confirmed() {
		return $this->_unconfirmed_history === false;
	}
	
	protected function _setup_reader() {
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
}