<?php
namespace AIP\lib\lang\fns;

class AIPLang_Function_THIS extends AIPLang_Function {
	protected $thing;
	protected $args;
	
	public static function parsable($line) {
		if(strpos($line, '$this->') === false) return false;
		
		if(!isset(\AIP\lib\Evaluer::$storage['instances']) or !isset(\AIP\lib\Evaluer::$storage['reflections']))
			return false;
		
		$path = \AIP\lib\Evaluer::pathenize();
		
		if(!isset(\AIP\lib\Evaluer::$storage['instances'][$path]) or !isset(\AIP\lib\Evaluer::$storage['reflections'][$path]))
			return false;
			
		$reflection = \AIP\lib\Evaluer::$storage['reflections'][$path];
		$instance = \AIP\lib\Evaluer::$storage['instances'][$path];
		
		return $reflection instanceof \ReflectionClass and is_object($instance);
	}
	
	public static function parse($line) {
		$name = self::extract_name($line, '$this->');
		try {
			$function = self::extract_function($line, $name);
			$arguments = self::extract_args($function);
		}
		catch(\AIP\excptns\lib\lang\fns\AIPLang_Function_NotValidFunctionException $e) {
			$function = false;
			$arguments = false;
		}
		
		$fake_name = substr($name, 7);
		
		if($function !== false)
			$fake_name = "{$fake_name}()";
			
		if($function === false) {
			try {
				$arguments = self::extract_setter_value($line, $name);
				$line = $name;
			}
			catch(\AIP\excptns\lib\lang\fns\AIPLang_Function_NotValidSetterException $e) {
				$arguments = false;
			}
		}
			
		if(!empty($arguments))
			$arguments = ", {$arguments}";
		
		$real = '\AIP\lib\lang\fns\AIPLang_Function_THIS::execute(\'' . $fake_name . '\'' . $arguments . ')';
		
		return $function === false ? str_replace($name, $real, $line) : str_replace($function, $real, $line);
	}
	
	public static function execute($thing) {
		$args = func_get_args();
		array_shift($args);
		
		$fn = new self($thing, $args);
		return $fn->this();
	}
	
	public function __construct($thing, $args) {
		\AIP\lib\Evaluer::init_storage('instances', array());
		\AIP\lib\Evaluer::init_storage('reflections', array());
		
		$this->thing = $thing;
		$this->args = $args;
	}
	
	public function this() {
		$path = \AIP\lib\Evaluer::pathenize();
		
		$reflection = isset(\AIP\lib\Evaluer::$storage['reflections'][$path]) ?
			\AIP\lib\Evaluer::$storage['reflections'][$path] : false;
			
		$instance = isset(\AIP\lib\Evaluer::$storage['instances'][$path]) ?
			\AIP\lib\Evaluer::$storage['instances'][$path] : false;
			
		if(!$reflection instanceof \ReflectionClass or !is_object($instance)) die('THIS::84');
		
		if(substr($this->thing, -2) === '()')
			return $this->_this_method($reflection, $instance);
		else
			return $this->_this_property($reflection, $instance);
	}
	
	protected function _this_method(\ReflectionClass $reflection, $instance) {
		$method = substr($this->thing, 0, -2);
		
		if(!$reflection->hasMethod($method)) die('THIS::95');
		
		$method = $reflection->getMethod($method);
		$accessible = $method->isPublic();
		
		$method->setAccessible(true);
		$return = $method->invokeArgs($instance, $this->args);
		$method->setAccessible($accessible);
		
		return $return;
	}
	
	protected function _this_property(\ReflectionClass $reflection, $instance) {
		$property = $this->thing;
		
		if(!$reflection->hasProperty($property)) die('THIS::110');
		
		$property = $reflection->getProperty($property);
		$accessible = $property->isPublic();
		
		$property->setAccessible(true);
		if(isset($this->args[0])) $property->setValue($instance, $this->args[0]);
		$return = $property->getValue($instance);
		$property->setAccessible($accessible);
		
		return $return;
	}
}