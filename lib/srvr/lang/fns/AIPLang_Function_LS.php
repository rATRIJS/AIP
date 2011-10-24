<?php
namespace AIP\lib\srvr\lang\fns;

class AIPLang_Function_LS extends AIPLang_Function {
	protected $target;
	protected $args;
	
	public static function parsable($line, $statement) {
		return substr($line, 0, 2) == 'ls' and (strlen($line) === 2 or substr($line, 0, 3) === 'ls ');
	}
	
	public static function parse($line, $statement) {
		$line = explode(' ', $line, 3);
		
		if(!isset($line[1])) $line[1] = "'.'";
		else $line[1] = \AIP\lib\Reflectionizer::parse_statement($line[1]);
		
		if(isset($line[2])) $line[2] = "'{$line[2]}'";
		else $line[2] = "''";
		
		return '\AIP\lib\lang\fns\AIPLang_Function_LS::execute(' . $line[1] . ', ' . $line[2] . ')';
	}
	
	public static function execute($target, $args = '') {
		$fn = new self($target, $args);
		$fn->ls();
		
		return \AIP\lib\hlprs\NotReturnable::i();
	}
	
	public function __construct($target, $args) {
		\AIP\lib\Evaluer::init_storage('reflections', array());
		
		$this->target = $target;
		$this->args = $args;
	}
	
	public function ls() {
		$current_reflection = self::get_current_reflection();
		
		if($this->target === '.' and false === $current_reflection)
			LS\_LS_NO_REFLECTION::init()->render();
			
		$reflection = '.' === $this->target ? $current_reflection : self::reflection_target_to_reflection($this->target);
		
		if($reflection instanceof \ReflectionClass)
			LS\_LS_CLASS::init($reflection, $this->args)->render();
			
		if($reflection instanceof \ReflectionMethod)
			LS\_LS_METHOD::init($reflection, $this->args)->render();
	}
}