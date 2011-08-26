<?php
namespace AIP\lib;

class Evaluer {
	public static $path = array();
	public static $reflections = array();
	
	protected static $sandbox_vars = array();
	
	public static function execute(Statement $statement) {
		$result = new Result;
		
		if($statement->in_block()) {
			$result->message = 'Not yet finished';
			
			return $result;
		}
		
		$result->php = $statement->to_php();
		
		ob_start();
		$result->return = self::sandboxed_eval($result->php);
		$result->output = ob_get_clean();
		
		return $result;
	}
	
	public static function ls($thing = '.', $args = array()) {
		if($thing === '.' and !isset(self::$reflections[self::pathenize()]))
			return self::ls_no_reflection();
		
		$reflection = $thing === '.' ? self::$reflections[self::pathenize()] : self::reflectionize($thing);
		
		if($reflection instanceof \ReflectionClass)
			return self::ls_class_reflection($reflection, $args);
	}
	
	public static function sandbox_vars($vars = null, $merge = true) {
		$path = self::pathenize();
		
		if(is_array($vars))
			self::$sandbox_vars[$path] = $merge ? array_merge(self::$sandbox_vars[$path], $vars) : $vars;
			
		return self::$sandbox_vars[$path];
	}
	
	public static function pathenize() {
		return '/' . implode('/', self::$path);
	}
	
	public static function reflectionize($thing) {
		$path = self::pathenize();
		
		if(is_object($thing) or class_exists($thing))
			self::$reflections[$path] new \ReflectionClass($thing);
	}
	
	public static function unreflectionize() {
		$path = self::pathenize();
		
		if(isset(self::$reflections[$path]))
			unset(self::$reflections[$path]);
	}
	
	protected static function sandboxed_eval($__aip_php) {
		$__aip_path = self::pathenize();
		
		if(isset(self::$sandbox_vars[$__aip_path]))
			extract(self::$sandbox_vars[$__aip_path], EXTR_SKIP);
		
		$__aip_return = eval($__aip_php);
		
		self::$sandbox_vars[$__aip_path] = get_defined_vars();
		unset(
			self::$sandbox_vars[$__aip_path]['__aip_path'],
			self::$sandbox_vars[$__aip_path]['__aip_php'],
			self::$sandbox_vars[$__aip_path]['__aip_return']
		);
		
		return $__aip_return;
	}
	
	protected static function ls_no_reflection() {
		var_dump(self::$sandbox_vars[self::pathenize()]);
	}
	
	protected static function ls_class_reflection(\ReflectionClass $reflection, $args = array()) {
		extract(self::_merge_args($args, array(
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
		)));
		
		$return = '';
		
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
				elseif($method->isPrivate()) $return .= 'private';
				
				if($method->isAbstract()) $return .= 'abstract ';
				elseif($method->isFinal()) $return .= 'final ';
				
				if($method->isStatic()) $return .= 'static ';
				
				$return .= 'function ';
				$return .= $method->name . "();\n";
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
				elseif($property->isPrivate()) $return .= 'private';

				if($property->isStatic()) $return .= 'static ';

				$return .= '$' . $property->name .  ";\n";
			}
		}
		
		echo $return;
	}
	
	protected static function _merge_args($args, $defaults) {
		foreach($args as $k => $v)
			if(!isset($defaults[$k]))
				unset($args[$k]);
				
		return array_merge($defaults, $args);
	}
}