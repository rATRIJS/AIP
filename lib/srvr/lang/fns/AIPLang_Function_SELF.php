<?php
namespace AIP\lib\srvr\lang\fns;

use \AIP\lib\srvr\evlr as Ev;
use \AIP\lib\srvr\prsr as P;

class AIPLang_Function_SELF extends AIPLang_Function {
	protected $_thing;
	protected $_args;
	
	public static function parsable($line, $statement) {
		return false !== strpos($line, 'self::');
	}
	
	public static function parse($line, $statement) {
		$current_reflection = Ev\Evaluer::reflection();
		
		if(!($current_reflection instanceof \ReflectionClass or $current_reflection instanceof \ReflectionMethod))
			return self::error_before_eval('Cannot use self in current scope.', 'You need to be inside class or method to use self.');
		
		extract(P\Helper::extract_name_function_arguments($line, 'self::'));
		
		$fake_name = substr($name, 6);
		if($function !== false) $fake_name = "{$fake_name}()";
			
		if(!empty($arguments))
			$arguments = ", {$arguments}";
		
		$real = self::_get_namespaced_self() . '::execute(\'' . $fake_name . '\'' . $arguments . ')';
		
		return $function === false ? str_replace($name, $real, $line) : str_replace($function, $real, $line);
	}
	
	public static function execute($thing) {
		$args = func_get_args();
		array_shift($args);
		
		$c = new self($thing, $args);
		return $c->self();
	}
	
	public function __construct($thing, $args) {
		$this->_thing = $thing;
		$this->_args = $args;
	}
	
	public function self() {
		$reflection = Ev\Evaluer::reflection();
		
		if($reflection instanceof \ReflectionMethod)
			$reflection = $reflection->getDeclaringClass();
		
		if(substr($this->_thing, -2) === '()')
			return $this->_self_method($reflection);
		else
			return $this->_self_property($reflection);
	}
	
	protected function _self_method(\ReflectionClass $reflection) {
		$method = substr($this->_thing, 0, -2);
		$class_name = $reflection->getName();
		
		if(!$reflection->hasMethod($method))
			return self::error_in_eval('Invalid method name', "Method {$class_name}:{$method}() doesn't exist.");
			
		$method = $reflection->getMethod($method);
		$method_name = $method->getName();
		$accessible = $method->isPublic();
		
		if(!$method->isStatic())
			return self::error_in_eval('Invalid method', "Method {$class_name}:{$method_name}() isn't static.");
		
		$method->setAccessible(true);
		$return = $method->invokeArgs(null, $this->_args);
		$method->setAccessible($accessible);
		
		return $return;
	}
	
	protected function _self_property(\ReflectionClass $reflection) {
		$property = substr($this->_thing, 1);
		$class_name = $reflection->getName();
		
		if(!$reflection->hasProperty($property))
			return self::error_in_eval('Invalid property name', "Property {$class_name}:\${$property} doesn't exist.");
		
		$property = $reflection->getProperty($property);
		$property_name = $property->getName();
		$accessible = $property->isPublic();
		
		if(!$property->isStatic())
			return self::error_in_eval('Invalid property.', "Property {$class_name}:\${$property_name} isn't static.");
		
		$property->setAccessible(true);
		if(isset($this->_args[0])) $property->setValue($this->_args[0]);
		$return = $property->getValue();
		$property->setAccessible($accessible);
		
		return $return;
	}
}