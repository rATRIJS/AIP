<?php
namespace AIP\lib\rdrs;

abstract class Reader {
	abstract public static function supported();
	abstract public function read($path);
	
	protected function _get_input_question($path) {
		return "{$path}$: ";
	}
}