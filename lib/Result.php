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
		
		$this->prepare_php($render);
		$this->prepare_return($render);
		$this->prepare_output($render);
		$this->prepare_message($render);
			
		return $this->prepare_render($render);
	}
	
	public function prepare_render($render) {
		$sep = "\n" . Formatter::load(str_repeat('- ', 25)) . "\n";
		
		return "\n" . implode($sep, $render) . "\n";
	}
	
	public function prepare_php(&$render = null) {
		$php = trim($this->php);
		if(empty($php)) return;
		
		$php = Formatter::load('# PHP:')->bold() . "\n" . $php;
		
		if(isset($render)) $render[] = $php;
		
		return $php;
	}
	
	public function prepare_return(&$render = null) {
		$return = null;
		if(is_scalar($this->return) or is_null($this->return)) {
			ob_start();
			var_dump($this->return);
			$return = ob_get_clean();
			$return = trim($return);
		}
		else {
			$return = print_r($this->return, true);
			$return = trim($return);
		}
		
		$return = Formatter::load('# Return')->bold() . "\n" . $return;
		
		if(isset($render)) $render[] = $return;
		
		return $return;
	}
	
	public function prepare_output(&$render = null) {
		$output = trim($this->output);
		if(empty($output)) return;
		
		$output = Formatter::load('# Output')->bold() . "\n" . $output;
		
		if(isset($render)) $render[] = $output;
		
		return $output;
	}
	
	public function prepare_message(&$render = null) {
		$message = trim($this->message);
		if(empty($message)) return;
		
		$message = Formatter::load($message)->bold();
		
		if(isset($render)) $render[] = $message;
		
		return $message;
	}
}