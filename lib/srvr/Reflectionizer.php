<?php
namespace AIP\lib\srvr;

class Reflectionizer {
	const _CLASS = 1;
	const _OBJECT = 2;
	const _METHOD = 3;
	const _FUNCTION = 4;
	
	protected $thing;
	protected $type;
	protected $reflection;
	
	public static function parse_statement($statement, $to_string = true) {
		if(is_string($statement) and substr($statement, -1) === ';') $statement = substr($statement, 0, -1);
		
		if(strpos($statement, '->')) $statement = explode('->', $statement);
		elseif(strpos($statement, '::')) $statement = explode('::', $statement);
		
		if(is_array($statement)) $statement = array_map('trim', $statement);
		
		if(is_array($statement) and $to_string) {
			$statement = str_replace(array("\n", "\t"), '', var_export($statement, true));
			if(substr($statement, -2) == ',)') $statement = substr($statement, 0, -2) . ')';
		}
		elseif($to_string) {
			$statement = "'{$statement}'";
		}
		
		return $statement;
	}
	
	public function __construct($thing) {
		$this->thing = $thing;
		
		$this->prepare();
	}
	
	public function reflectionize() {
		if(!isset($this->reflection)) {
			if(in_array($this->type, array(self::_CLASS, self::_OBJECT)))
				$this->reflection = new \ReflectionClass($this->thing);
			elseif($this->type === self::_METHOD)
				$this->reflection = new \ReflectionMethod($this->thing[0], substr($this->thing[1], 0, -2));
			elseif($this->type === self::_FUNCTION)
				$this->reflection = new \ReflectionFunction(substr($this->thing, 0, -2));
		}
		
		return $this->reflection;
	}
	
	public function locationize() {
		$reflection = $this->reflectionize();
		
		return $reflection->name;
	}
	
	protected function prepare() {
		if(is_array($this->thing)) {
			if(count($this->thing) !== 2) die('Reflectionizer::38'); // TODO : throw Exception
			
			$this->type = self::_METHOD;
		}
		elseif(is_object($this->thing)) {
			$this->type = self::_OBJECT;
		}
		elseif(class_exists($this->thing)) {
			$this->type = self::_CLASS;
		}
		elseif(substr($this->thing, -2) === '()') {
			$this->type = self::_FUNCTION;
		}
			
		return $this->type;
	}
}