<?php
namespace AIP\lib\srvr\lang\fns;

use \AIP\lib as L;

class AIPLang_Function_THIS extends AIPLang_Function {
	protected $thing;
	protected $args;
	
	public static function parsable($line, $statement) {
		return false !== strpos($line, '$this->');
	}
	
	public static function parse($line, $statement) {
		$current_reflection = self::get_current_reflection();
		$current_instance = self::get_current_instance();
		
		if(!($current_reflection instanceof \ReflectionClass or $current_reflection instanceof \ReflectionMethod))
			return self::error_before_eval('Cannot use $this in current scope.', 'You need to be inside class or method to use $this.');
		
		if(!is_object($current_instance))
			return self::error_before_eval('Cannot use $this in current scope.', 'You need to be inside object to use $this.');
		
		extract(self::extract_name_function_arguments($line, '$this->'));
		
		$fake_name = substr($name, 7);
		if($function !== false) $fake_name = "{$fake_name}()";
			
		if(!empty($arguments))
			$arguments = ", {$arguments}";
		
		$real = '\AIP\lib\lang\fns\AIPLang_Function_THIS::execute(\'' . $fake_name . '\'' . $arguments . ')';
		
		return $function === false ? str_replace($name, $real, $line) : str_replace($function, $real, $line);
	}
	
	public static function execute($thing) {
		$args = func_get_args();
		array_shift($args);
		
		$c = new self($thing, $args);
		return $c->this();
	}
	
	public function __construct($thing, $args) {
		L\Evaluer::init_storage('instances', array());
		L\Evaluer::init_storage('reflections', array());
		
		$this->thing = $thing;
		$this->args = $args;
	}
	
	public function this() {
		$reflection = self::get_current_reflection();
		$instance = self::get_current_instance();
		
		if(substr($this->thing, -2) === '()')
			return $this->_this_method($reflection, $instance);
		else
			return $this->_this_property($reflection, $instance);
	}
	
	protected function _this_method(\ReflectionClass $reflection, $instance) {
		$method = substr($this->thing, 0, -2);
		$class_name = $reflection->getName();
		
		if(!$reflection->hasMethod($method))
			return self::error_in_eval('Invalid method name', "Method {$class_name}:{$method}() doesn't exist.");
		
		$method = $reflection->getMethod($method);
		$method_name = $method->getName();
		$accessible = $method->isPublic();
		
		if($method->isStatic())
			return self::error_in_eval('Invalid method', "Method {$class_name}:{$method_name}() is static.");
		
		$method->setAccessible(true);
		$return = $method->invokeArgs($instance, $this->args);
		$method->setAccessible($accessible);
		
		return $return;
	}
	
	protected function _this_property(\ReflectionClass $reflection, $instance) {
		$property = $this->thing;
		$class_name = $reflection->getName();
		
		if(!$reflection->hasProperty($property))
			return self::error_in_eval('Invalid property name', "Property {$class_name}:\${$method}() doesn't exist.");
		
		$property = $reflection->getProperty($property);
		$property_name = $property->getName();
		$accessible = $property->isPublic();
		
		if($property->isStatic())
			return self::error_in_eval('Invalid property.', "Property {$class_name}:\${$property_name} is static.");
		
		$property->setAccessible(true);
		if(isset($this->args[0])) $property->setValue($instance, $this->args[0]);
		$return = $property->getValue($instance);
		$property->setAccessible($accessible);
		
		return $return;
	}
}