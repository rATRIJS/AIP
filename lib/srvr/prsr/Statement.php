<?php
namespace AIP\lib\srvr\prsr;

class Statement {
	protected $_php;
	protected $_interrupted;
	protected $_block_level;
	protected $_aip;
	
	public function __construct($php = false) {
		$this->_php = array();
		$this->_interrupted = false;
		$this->_block_level = 0;
		$this->_aip = false;
		
		if(false !== $php) {
			if(is_array($php)) $this->_php = $php;
			else $this->_php[] = $php;
		}
	}
	
	public function append($php) {
		$this->_php[] = $php;
		
		return $this;
	}
	
	public function set_aip($aip) {
		$this->_aip = $aip;
	}
	
	public function interrupted($interrupted = null) {
		if(!is_bool($interrupted)) return $this->_interrupted;
		
		$this->_interrupted = $interrupted;
		
		return $this;
	}
	
	public function multiline() {
		return count($this->_php) > 1;
	}
	
	public function in_block() {
		return $this->_block_level > 0;
	}
	
	public function increase_block_level() {
		$this->_block_level++;
		
		return $this;
	}
	
	public function decrease_block_level() {
		if($this->_block_level > 0)
			$this->_block_level--;
		
		return $this;
	}
	
	public function to_php() {
		return implode(" ", $this->_php);
	}
	
	public function to_aip() {
		return $this->_aip;
	}
}