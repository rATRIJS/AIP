<?php
namespace AIP\lib\lang;

abstract class AIPLang_Construct {
	abstract public static function parsable($line);
	abstract public static function parse($line);
	
	protected function merge_args($defaults, $args) {
		foreach($args as $k => $v)
			if(!isset($defaults[$k]))
				unset($args[$k]);
				
		return array_merge($defaults, $args);
	}
}