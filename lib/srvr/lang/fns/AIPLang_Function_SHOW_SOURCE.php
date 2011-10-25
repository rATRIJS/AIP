<?php
namespace AIP\lib\srvr\lang\fns;

use \AIP\lib\srvr\evlr as Ev;

class AIPLang_Function_SHOW_SOURCE extends AIPLang_Function {
	protected $target;
	
	public static function parsable($line, $statement) {
		return (substr($line, 0, 11) === 'show-source' and (strlen($line) === 11 or substr($line, 11, 1) === ' '));
	}
	
	public static function parse($line, $statement) {
		$line = explode(' ', $line);
		
		if(!isset($line[1])) $line[1] = "'.'";
		else $line[1] = \AIP\lib\srvr\Reflectionizer::parse_statement($line[1]);
			
		return self::_get_namespaced_self() . '::execute(' . $line[1] . ')';
	}
	
	public static function execute($target) {
		$fn = new self($target);
		$fn->show_source();
		
		return \AIP\lib\hlprs\NotReturnable::i();
	}
	
	public function __construct($target) {
		$this->target = $target;
	}
	
	public function show_source() {
		$reflection = false;
		$current_reflection = Ev\Evaluer::reflection();
		
		if($this->target === '.') $reflection = $current_reflection;
		else $reflection = self::reflection_target_to_reflection($this->target);
		
		$this->_make_internal_message($reflection);
		
		echo $this->_extract_php($reflection->getFileName(), $reflection->getStartLine(), $reflection->getEndLine());
	}
	
	protected function _make_internal_message(\Reflector $reflection) {
		$type = false;
		$name = false;
		$location = false;
		
		if($reflection instanceof \ReflectionFunction) {
			$type = 'function';
			$name = $reflection->name;
		}
		elseif($reflection instanceof \ReflectionClass) {
			$type = 'class';
			$name = $reflection->name;
		}
		elseif($reflection instanceof \ReflectionMethod) {
			$type = 'method';
			$name = $reflection->getDeclaringClass()->name . '::' . $reflection->name;
		}
		
		$location = $reflection->getFileName() . ':' . $reflection->getStartLine();
		
		Ev\Evaluer::make_internal_from(
			Ev\Evaluer::SOURCE_OUTPUT,
			sprintf("Source Code for %s '%s' (%s)", $type, $name, $location)
		);
	}
	
	protected function _extract_php($file, $start, $end) {
		$file = file($file);
		
		$offset = $start - 1;
		$length = $end - $offset;
		$php = array_slice($file, $offset, $length);
		
		$tab_size = strspn($php[0], "\t");
		$tab_string = implode(array_pad(array(), $tab_size, "\t"));
		if($tab_size > 0) {
			foreach($php as &$line)
				if(substr($line, 0, $tab_size) === $tab_string)
					$line = substr($line, $tab_size);
		}
		
		return implode("", $php);
	}
}