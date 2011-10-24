<?php
namespace AIP\lib\srvr;

class Debugger {
	protected function __construct() {}
		
	public static function debug($vars) {
		ob_get_clean();
		
		Evaluer::$path[] = 'debug';
		Evaluer::sandbox_vars($vars, false);
		
		$parser = new Parser;
		$interrupted = false;
		while(!$interrupted) {
			$line = Input::read(Evaluer::pathenize());
			$statement = $parser->parse($line);
			$result = Evaluer::execute($statement);
			Output::write($result);
			
			$interrupted = $statement->interrupted();
		}
		
		$vars = Evaluer::sandbox_vars();
		array_pop(Evaluer::$path);
		
		ob_start();
		
		return $vars;
	}
}