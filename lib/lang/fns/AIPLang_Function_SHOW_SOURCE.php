<?php
namespace AIP\lib\lang\fns;

class AIPLang_Function_SHOW_SOURCE extends AIPLang_Function {
	protected $target;
	
	public static function parsable($line) {
		return (substr($line, 0, 11) === 'show-source' and (strlen($line) === 11 or substr($line, 11, 1) === ' '));
	}
	
	public static function parse($line) {
		$line = explode(' ', $line);
		
		if(!isset($line[1])) $line[1] = "'.'";
		else $line[1] = \AIP\lib\Reflectionizer::parse_statement($line[1]);
			
		return '\AIP\lib\lang\fns\AIPLang_Function_SHOW_SOURCE::execute(' . $line[1] . ')';
	}
	
	public static function execute($target) {
		$fn = new self($target);
		return $fn->show_source();
	}
	
	public function __construct($target) {
		\AIP\lib\Evaluer::init_storage('reflections', array());
		
		$this->target = $target;
	}
	
	public function show_source() {
		$reflection = false;
		$current_reflection = self::get_current_reflection();
		
		if($this->target === '.') $reflection = $current_reflection;
		else $reflection = self::reflection_target_to_reflection($this->target);
		
		if(!$reflection instanceof \Reflector) { echo 'SHOWSOURCE::46'; return; };
		
		echo $this->_extract_php($reflection->getFileName(), $reflection->getStartLine(), $reflection->getEndLine());
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