<?php
namespace AIP\lib\srvr\lang\fns;

use \AIP\lib\srvr\prsr as P;
use \AIP\lib\srvr\evlr as Ev;

class AIPLang_Function_CD extends AIPLang_Function {
	protected $_thing;
	
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
		
		$line[1] = P\Helper::quotenize($line[1]);
			
		return self::_get_namespaced_self() . '::execute(' . $line[1] . ')';
	}
	
	public static function execute($thing) {
		$fn = new self($thing);
		$fn->cd();
		
		return \AIP\lib\hlprs\NotReturnable::i();
	}
	
	public function __construct($thing) {
		$this->_thing = $thing;
	}
	
	public function cd() {
		if($this->_thing === '.') return $this->_cd_self();
		elseif($this->_thing === '..') return $this->_cd_parent();
		
		return $this->_cd_thing();
	}
	
	protected function _cd_self() {
		return true;
	}
	
	protected function _cd_parent() {
		Ev\Evaluer::reflection(null);
		Ev\Evaluer::instance(null);
		Ev\Evaluer::sandbox_vars(array(), false);
		
		array_pop(Ev\Evaluer::$path);
		
		return true;
	}
	
	protected function _cd_thing() {
		$current_reflection = Ev\Evaluer::reflection();
		
		if(is_array($this->_thing)) {
			if($this->_thing[0] === '$this') {
				if(!$current_reflection instanceof \ReflectionClass) die('CD::73'); // TODO : throw Exception
				
				$this->_thing[0] = $current_reflection->name;
			}
		}
		
		$r = new \AIP\lib\srvr\Reflectionizer($this->_thing);
		
		Ev\Evaluer::$path[] = $r->locationize();
		Ev\Evaluer::reflection($r->reflectionize());
		Ev\Evaluer::sandbox_vars(array(), false);
		
		if(is_object($this->_thing))
			Ev\Evaluer::instance($this->_thing);
		
		return true;
	}
}