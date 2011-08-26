<?php
namespace AIP\lib;

class Reflectionizer {
	const _CLASS = 1;
	const _OBJECT = 2;
	const _METHOD = 3;
	
	protected $thing;
	protected $type;
	protected $reflection;
	
	public function __construct($thing) {
		$this->thing = $thing;
		
		$this->prepare();
	}
	
	public function reflectionize() {
		if(!isset($this->reflection)) {
			if(in_array($this->type, array(self::_CLASS, self::_OBJECT)))
				$this->reflection = new \ReflectionClass($this->thing);
			elseif($this->type === self::_METHOD)
				$this->reflection = new \ReflectionMethod($this->thing[0], $this->thing[1]);
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
			
		return $this->type;
	}
}