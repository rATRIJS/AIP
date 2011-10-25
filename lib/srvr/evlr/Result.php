<?php
namespace AIP\lib\srvr\evlr;

use \AIP\lib as L;
use \AIP\lib\hlprs as H;

class Result {
	public $return;
	public $output;
	public $message;
	public $php;
	public $internal;
	
	public static function __callStatic($name, $args) {
		if(property_exists(__CLASS__, $name) and isset($args[0]))
			return new self(array($name => $args[0]));
			
		throw new \AIP\excptns\InvalidMethodException("'{$name}' isn't a valid method for " . __CLASS__ . " class");
	}
	
	public function __construct($args = array()) {
		if(!array_key_exists('return', $args))
			$args['return'] = new H\NotReturnable;
		
		foreach($args as $k => $v)
			if(property_exists($this, $k))
				$this->{$k} = $v;
	}
	
	public function render() {
		$render = array();
		
		if(L\Config::get(L\Config::OPTION_VERBOSITY) > 0)
			$this->prepare_php($render);
		
		$internal = $this->prepare_internal();
		if(!isset($internal)) {
			$this->prepare_return($render);
			$this->prepare_output($render);
			$this->prepare_message($render);
		}
		else {
			$render[] = $internal;
		}
			
		return $this->prepare_render($render);
	}
	
	public function prepare_render($render) {
		$sep = "\n" . H\Formatter::load(str_repeat('- ', 25)) . "\n";
		
		return "\n" . implode($sep, $render) . "\n";
	}
	
	public function prepare_php(&$render = null) {
		$php = trim($this->php);
		if(empty($php)) return;
		
		$php = H\Formatter::load('# PHP')->bold() . "\n" . $php;
		
		if(isset($render)) $render[] = $php;
		
		return $php;
	}
	
	public function prepare_return(&$render = null) {
		if($this->return instanceof H\NotReturnable) return;
		
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
		
		$return = H\Formatter::load('# Return')->bold() . "\n" . $return;
		
		if(isset($render)) $render[] = $return;
		
		return $return;
	}
	
	public function prepare_output(&$render = null) {
		$output = trim($this->output);
		if(empty($output)) return;
		
		$output = H\Formatter::load('# Output')->bold() . "\n" . $output;
		
		if(isset($render)) $render[] = $output;
		
		return $output;
	}
	
	public function prepare_message(&$render = null) {
		$message = trim($this->message);
		if(empty($message)) return;
		
		$message = H\Formatter::load($message)->bold();
		
		if(isset($render)) $render[] = $message;
		
		return $message;
	}
	
	public function prepare_internal(&$render = null) {
		if(!is_array($this->internal)) return;
		
		$title = trim($this->internal['title']);
		$body = trim($this->internal['body']);
		if(empty($body) or empty($title)) return;
		
		$internal = H\Formatter::load("# {$title}")->bold() . "\n" . $body;
		
		if(isset($render)) $render[] = $internal;
		
		return $internal;
	}
}