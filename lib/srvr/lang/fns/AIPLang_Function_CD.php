<?php
namespace AIP\lib\srvr\lang\fns;

class AIPLang_Function_CD extends AIPLang_Function {
	protected $thing;
	
	public static function parsable($line, $statement) {
		return substr($line, 0, 2) == 'cd' and (strlen($line) === 2 or substr($line, 0, 3) === 'cd ');
	}
	
	public static function parse($line, $statement) {
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
		
		$line[1] = self::quotenize($line[1]);
			
		return '\AIP\lib\lang\fns\AIPLang_Function_CD::execute(' . $line[1] . ')';
	}
	
	public static function execute($thing) {
		$fn = new self($thing);
		$fn->cd();
		
		return \AIP\lib\hlprs\NotReturnable::i();
	}
	
	public function __construct($thing) {
		\AIP\lib\Evaluer::init_storage('reflections', array());
		\AIP\lib\Evaluer::init_storage('instances', array());
		
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
		unset(\AIP\lib\Evaluer::$storage['reflections'][\AIP\lib\Evaluer::pathenize()]);
		unset(\AIP\lib\Evaluer::$storage['instances'][\AIP\lib\Evaluer::pathenize()]);
		\AIP\lib\Evaluer::sandbox_vars(array(), false);
		
		array_pop(\AIP\lib\Evaluer::$path);
		
		return true;
	}
	
	protected function _cd_thing() {
		$current_path = \AIP\lib\Evaluer::pathenize();
		$current_reflection = isset(\AIP\lib\Evaluer::$storage['reflections'][$current_path]) ?
			\AIP\lib\Evaluer::$storage['reflections'][$current_path] : false;
		
		if(is_array($this->thing)) {
			if($this->thing[0] === '$this') {
				if(!$current_reflection instanceof \ReflectionClass) die('CD::73'); // TODO : throw Exception
				
				$this->thing[0] = $current_reflection->name;
			}
		}
		
		$r = new \AIP\lib\Reflectionizer($this->thing);
		
		\AIP\lib\Evaluer::$path[] = $r->locationize();
		\AIP\lib\Evaluer::$storage['reflections'][\AIP\lib\Evaluer::pathenize()] = $r->reflectionize();
		\AIP\lib\Evaluer::sandbox_vars(array(), false);
		
		if(is_object($this->thing))
			\AIP\lib\Evaluer::$storage['instances'][\AIP\lib\Evaluer::pathenize()] = $this->thing;
		
		return true;
	}
}