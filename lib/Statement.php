<?php
namespace AIP\lib;

class Statement {
	protected $php;
	protected $interrupted;
	protected $block_level;
	
	public function __construct($php = false) {
		$this->php = array();
		$this->interrupted = false;
		$this->block_level = 0;
		
		if(false !== $php) {
			if(is_array($php)) $this->php = $php;
			else $this->php[] = $php;
		}
	}
	
	public function append($php) {
		$this->php[] = $php;
		
		return $this;
	}
	
	public function interrupted($interrupted = null) {
		if(!is_bool($interrupted)) return $this->interrupted;
		
		$this->interrupted = $interrupted;
		
		return $this;
	}
	
	public function multiline() {
		return count($this->php) > 1;
	}
	
	public function in_block() {
		return $this->block_level > 0;
	}
	
	public function increase_block_level() {
		$this->block_level++;
		
		return $this;
	}
	
	public function decrease_block_level() {
		if($this->block_level > 0)
			$this->block_level--;
		
		return $this;
	}
	
	public function to_php() {
		return implode(" ", $this->php);
	}
}