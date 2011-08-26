<?php
namespace AIP\lib;

class Result {
	public $return;
	public $output;
	public $message;
	public $php;
	public $path;
	
	public function __construct($args = array()) {
		foreach($args as $k => $v)
			if(property_exists($this, $k))
				$this->{$k} = $v;
	}
	
	public function render() {
		$render = array();
		
		if(!empty($this->php))
			$render[] = "PHP:\n{$this->php}";
		
		if(!empty($this->return))
			$render[] = "Return:\n{$this->return}";
		
		if(!empty($this->output))
			$render[] = "Output:\n{$this->output}";
			
		if(!empty($this->message))
			$render[] = "Message:\n{$this->message}";
			
		return implode("\n\n\n", $render);
	}
}