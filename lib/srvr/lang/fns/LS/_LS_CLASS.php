<?php
namespace AIP\lib\srvr\lang\fns\LS;

class _LS_CLASS {
	protected $_reflection;
	protected $_args;
	
	public function init(\ReflectionClass $reflection, $args) {
		return new self($reflection, $args);
	}
	
	public function __construct(\ReflectionClass $reflection, $args) {
		$this->_reflection = $reflection;
		$this->_args = $this->_parse_args($args);
	}
	
	public function render() {
		extract($this->_args);
		
		$output = '';
		
		if($show_constants) $this->_prepare_constants($output);
		if($show_properties) $this->_prepare_properties($output);
		if($show_methods) $this->_prepare_methods($output);
		
		echo $output;
	}
	
	protected function _prepare_constants(&$output) {
		$output .= "Constants: \n";
		
		$constants = $this->_reflection->getConstants();
		
		foreach($constants as $constant_name => $constant_value) {
			if(is_string($constant_value))
				$constant_value = "'{$constant_value}'";
			
			$output .= "\t- const {$constant_name} = {$constant_value};\n";
		}
	}
	
	protected function _prepare_properties(&$output) {
		extract($this->_args);
		
		$output .= "Properties:\n";
		
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
			
		$properties = $this->_reflection->getProperties($filter);

		foreach($properties as $property) {
			$output .= "\t- ";

			if($property->isPublic()) $output .= 'public ';
			elseif($property->isProtected()) $output .= 'protected ';
			elseif($property->isPrivate()) $output .= 'private ';

			if($property->isStatic()) $output .= 'static ';

			$output .= '$' . $property->name .  ";\n";
		}
	}
	
	protected function _prepare_methods(&$output) {
		extract($this->_args);
		
		$output .= "Methods:\n";
		
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
				
		$methods = $this->_reflection->getMethods($filter);
		
		foreach($methods as $method) {
			$declared_class = $method->getDeclaringClass();
			
			$is_inherited = $this->_reflection->getName() !== $declared_class->getName();
			if($is_inherited and !in_array('inherited', $types)) continue;
			
			$output .= "\t- ";
			
			if($method->isPublic()) $output .= 'public ';
			elseif($method->isProtected()) $output .= 'protected ';
			elseif($method->isPrivate()) $output .= 'private ';
			
			if($method->isAbstract()) $output .= 'abstract ';
			elseif($method->isFinal()) $output .= 'final ';
			
			if($method->isStatic()) $output .= 'static ';
			
			$output .= 'function ';
			if($is_inherited) $output .= $declared_class->getName() . '::';
			$output .= $method->name . "();\n";
		}
	}
	
	protected function _parse_args($args) {
		$args = \AIP\lib\Optionizer::init($args, array(
			'show_methods' => array(
				'keys' => array('m', 'methods')
			),
			'show_properties' => array(
				'keys' => array('p', 'properties')
			),
			'show_constants' => array(
				'keys' => array('c', 'constants')
			)
		))->parse();
		
		$show_switches = array(
			'show_methods' => true,
			'show_properties' => true,
			'show_constants' => true
		);
		
		$found = false;
		foreach(array_keys($show_switches) as $switch) {
			if(isset($args[$switch])) {
				$found = true;
			}
		}
		
		if(!$found) $args = array_merge($show_switches, $args);
		
		$args['types'] = array(
			'public',
			'protected',
			'private',
			'final',
			'static',
			'abstract',
			'inherited'
		);
		
		return $args;
	}
}