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
			
		if(substr($line[1], 0, 5) === '$this') {
			if(strlen($line[1]) === 5) {
				$line[1] = '.';
			}
			else {
				$line[1] = explode('->', $line[1], 2);
				if(substr($line[1][1], -2) === '()')
					$line[1][1] = substr($line[1][1], 0, -2);
				$line[1] = "array('\$this', '{$line[1][1]}')";
			}
		}
			
		if(!in_array(substr($line[1], 0, 1), array('$', '\'', '"')) and substr($line[1], -1) !== ')')
			$line[1] = "'{$line[1]}'";
			
		return '\AIP\lib\lang\fns\AIPLang_Function_CD::execute(' . $line[1] . ')';
	}
	
	public static function execute($thing) {
		$fn = new self($thing);
		
		$fn->cd();
	}
	
	public function __construct($thing) {
		$this->thing = $thing;
	}
	
	public function cd() {
		if($this->thing === '.') return $this->_cd_self();
		elseif($this->thing === '..') return $this->_cd_parent();
		
		return $this->_cd_thing();
	}
	
	protected function _cd_self() {
		return true;
	}
	
	protected function _cd_parent() {
		unset(\AIP\lib\Evaluer::$reflections[\AIP\lib\Evaluer::pathenize()]);
		\AIP\lib\Evaluer::sandbox_vars(array(), false);
		
		array_pop(\AIP\lib\Evaluer::$path);
		
		return true;
	}
	
	protected function _cd_thing() {
		$current_path = \AIP\lib\Evaluer::pathenize();
		$current_reflection =
			isset(\AIP\lib\Evaluer::$reflections[$current_path]) ? \AIP\lib\Evaluer::$reflections[$current_path] : false;
		
		if(is_array($this->thing)) {
			if($this->thing[0] === '$this') {
				if(!$current_reflection instanceof \ReflectionClass) die('CD::73'); // TODO : throw Exception
				
				$this->thing[0] = $current_reflection->name;
			}
		}
		
		$r = new \AIP\lib\Reflectionizer($this->thing);
		
		\AIP\lib\Evaluer::$path[] = $r->locationize();
		\AIP\lib\Evaluer::$reflections[\AIP\lib\Evaluer::pathenize()] = $r->reflectionize();
		\AIP\lib\Evaluer::sandbox_vars(array(), false);
		
		return true;
	}
}