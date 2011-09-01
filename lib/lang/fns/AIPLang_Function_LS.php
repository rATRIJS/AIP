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
		
		if(!isset($line[1])) $line[1] = "'.'";
		else $line[1] = \AIP\lib\Reflectionizer::parse_statement($line[1]);
		
		return '\AIP\lib\lang\fns\AIPLang_Function_LS::execute(' . $line[1] . ')';
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
		$current_reflection = self::get_current_reflection();
		
		if($this->target === '.' and false === $current_reflection)
			return $this->_ls_no_reflection();
			
		$reflection = '.' === $this->target ? $current_reflection : self::reflection_target_to_reflection($this->target);
		
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
				'abstract',
				'inherited'
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
				$declared_class = $method->getDeclaringClass();
				
				$is_inherited = $reflection->getName() !== $declared_class->getName();
				if($is_inherited and !in_array('inherited', $types)) continue;
				
				$return .= "\t- ";
				
				if($method->isPublic()) $return .= 'public ';
				elseif($method->isProtected()) $return .= 'protected ';
				elseif($method->isPrivate()) $return .= 'private ';
				
				if($method->isAbstract()) $return .= 'abstract ';
				elseif($method->isFinal()) $return .= 'final ';
				
				if($method->isStatic()) $return .= 'static ';
				
				$return .= 'function ';
				if($is_inherited) $return .= $declared_class->getName() . '::';
				$return .= $method->name . "();\n";
			}
		}
		
		echo $return;
	}
	
	protected function _ls_method_reflection(\ReflectionMethod $reflection) {
		extract($this->merge_args(array(
			'types' => array(
				'optional',
				'required',
				'by_reference'
			)
		), $this->args));
		
		$parameters = $reflection->getParameters();
		
		$return = "Arguments:\n";
		foreach($parameters as $parameter) {
			$is_optional = $parameter->isOptional();
			$is_required = !$is_optional;
			$is_passed_by_reference = $parameter->isPassedByReference();
			
			if($is_optional and !in_array('optional', $types)) continue;
			if($is_required and !in_array('required', $types)) continue;
			if($is_passed_by_reference and !in_array('by_reference', $types)) continue;
			
			$return .= "\t- ";
			if($is_passed_by_reference) $return .= '&';
			$return .= '$' . $parameter->getName() . ' ';
			if($is_optional) $return .= '= ' . str_replace("\n", '', var_export($parameter->getDefaultValue(), true));
			$return .= "\n";
		}
		
		echo $return;
	}
}