<?php
namespace AIP\lib\lang\fns;

class AIPLang_Function_CD extends AIPLang_Function {
	protected $thing;
	
	public static function parsable($line) {
		return substr($line, 0, 2) == 'cd' and (strlen($line) === 2 or substr($line, 0, 3) === 'cd ');
	}
	
	public static function parse($line) {
		$line = explode(' ', $line, 2);
		
		if(!isset($line[1]))
			$line[1] = '.';
			
		if(substr($line[1], -1) === ';')
			$line[1] = substr($line[1], 0, -1);
			
		if(!in_array(substr($line[1], 0, 1), array('$', '\'', '"')))
			$line[1] = "'{$line[1]}'";
			
		return "\AIP\lib\Evaluer::cd({$line[1]})";
	}
	
	public static function execute($thing) {
		$fn = new self($thing);
		
		$fn->cd();
		
		$reflection = self::reflectionize($thing);
		
		self::$path[] = is_object($thing) ? get_class($thing) . '#[ID]' : $thing;
		
		self::$sandbox_vars[self::pathenize()] = array();
		self::$reflections[self::pathenize()] = $reflection;
	}
	
	public function __construct($thing) {
		$this->thing = $thing;
	}
	
	public function cd() {
		if($this->thing === '.') return $this->_cd_self();
		elseif($this->thing === '..') return $this->_cd_parent();
	}
	
	protected function _cd_self() {
		return true;
	}
	
	protected function _cd_parent() {
		\AIP\lib\Evaluer::unreflectionize();
		\AIP\lib\Evaluer::sandbox_vars(array(), false);
		
		array_pop(\AIP\lib\Evaluer::$path);
		
		return true;
	}
	
	protected function _cd_thing() {
		\AIP\lib\Evaluer::reflectionize($this->thing);
		\AIP\lib\Evaluer::$path[] = is_object($this->thing) ? get_class($this->thing) . '*' : $thing;
		\AIP\lib\Evaluer::sandbox_vars(array(), false);
	}
}