<?php
namespace AIP\lib\lang\fns;

class AIPLang_Function_LS extends AIPLang_Function {
	protected $target;
	protected $args;
	
	public static function parsable($line) {
		return substr($line, 0, 2) == 'ls' and (strlen($line) === 2 or substr($line, 0, 3) === 'ls ');
	}
	
	public static function parse($line) {
		$line = explode(' ', $line, 2);
		
		if(!isset($line[1]))
			$line[1] = '.';
			
		$args = explode(' ', $line[1]);
		$target = '.';
		if(substr($args[count($args) - 1], 0, 1) !== '-')
			$target = array_pop($args);
			
		if(!in_array(substr($target, 0, 1), array('\'', '"', '$')))
			$target = "'{$target}'";
		
		return '\AIP\lib\lang\fns\AIPLang_Function_LS::execute(' . $target . ')';
	}
	
	public static function execute($target, $args = array()) {
		$fn = new self($target, $args);
		$fn->ls();
	}
	
	public function __construct($target, $args) {
		\AIP\lib\Evaluer::init_storage('reflections', array());
		
		$this->target = $target;
		$this->args = $args;
	}
	
	public function ls() {
		$path = \AIP\lib\Evaluer::pathenize();
		$reflection =
			isset(\AIP\lib\Evaluer::$storage['reflections'][$path]) ? \AIP\lib\Evaluer::$storage['reflections'][$path] : false;
		
		if($this->target === '.' and false === $reflection)
			return $this->_ls_no_reflection();
			
		if($this->target !== '.') {
			$reflection = new \AIP\lib\Reflectionizer($this->target);
			$reflection = $reflection->reflectionize();
		}
		
		if($reflection instanceof \ReflectionClass)
			return $this->_ls_class_reflection($reflection);
			
		if($reflection instanceof \ReflectionMethod)
			return $this->_ls_method_reflection($reflection);
	}
	
	protected function _ls_no_reflection() {
		var_dump(\AIP\lib\Evaluer::sandbox_vars());
	}
	
	protected function _ls_class_reflection(\ReflectionClass $reflection) {
		extract($this->merge_args(array(
			'show_methods' => true,
			'show_properties' => true,
			'show_constants' => true,
			'show_parents' => true,
			'show_interfaces' => true,
			'types' => array(
				'public',
				'protected',
				'private',
				'final',
				'static',
				'abstract'
			)
		), $this->args));
		
		$return = '';
		
		if($show_constants) {
			$return .= "Constants: \n";
			
			$constants = $reflection->getConstants();
			
			foreach($constants as $constant_name => $constant_value) {
				if(is_string($constant_value))
					$constant_value = "'{$constant_value}'";
				
				$return .= "\t- const {$constant_name} = {$constant_value};\n";
			}
		}
		
		if($show_properties) {
			$return .= "Properties:\n";
			
			$type_map = array(
				'public' => \ReflectionMethod::IS_PUBLIC,
				'protected' => \ReflectionProperty::IS_PROTECTED,
				'private' => \ReflectionProperty::IS_PRIVATE,
				'static' => \ReflectionProperty::IS_STATIC
			);
			
			$filter = array_sum($type_map);
			foreach($type_map as $k => $v)
				if(!in_array($k, $types))
					$filter -= $type_map[$k];
				
			$properties = $reflection->getProperties($filter);

			foreach($properties as $property) {
				$return .= "\t- ";

				if($property->isPublic()) $return .= 'public ';
				elseif($property->isProtected()) $return .= 'protected ';
				elseif($property->isPrivate()) $return .= 'private ';

				if($property->isStatic()) $return .= 'static ';

				$return .= '$' . $property->name .  ";\n";
			}
		}
		
		if($show_methods) {
			$return .= "Methods:\n";
			
			$type_map = array(
				'public' => \ReflectionMethod::IS_PUBLIC,
				'protected' => \ReflectionMethod::IS_PROTECTED,
				'private' => \ReflectionMethod::IS_PRIVATE,
				'final' => \ReflectionMethod::IS_FINAL,
				'static' => \ReflectionMethod::IS_STATIC,
				'abstract' => \ReflectionMethod::IS_ABSTRACT
			);
			
			$filter = array_sum($type_map);
			foreach($type_map as $k => $v)
				if(!in_array($k, $types))
					$filter -= $type_map[$k];
					
			$methods = $reflection->getMethods($filter);
			
			foreach($methods as $method) {
				$return .= "\t- ";
				
				if($method->isPublic()) $return .= 'public ';
				elseif($method->isProtected()) $return .= 'protected ';
				elseif($method->isPrivate()) $return .= 'private ';
				
				if($method->isAbstract()) $return .= 'abstract ';
				elseif($method->isFinal()) $return .= 'final ';
				
				if($method->isStatic()) $return .= 'static ';
				
				$return .= 'function ';
				$return .= $method->name . "();\n";
			}
		}
		
		echo $return;
	}
	
	protected function _ls_method_reflection(\ReflectionMethod $reflection) {
		var_dump($reflection->__toString());
		var_dump($reflection->getParameters());
	}
}