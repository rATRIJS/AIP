<?php
namespace AIP\lib;

class REPL {
	protected static $parser;
	
	public static function init() {
		self::$parser = new Parser;
		
		$config = require _AIP_PATH . '/config.php';
		
		if(isset($config['bootloader'])) {
			$exec = explode("\n", $config['bootloader']);
			
			foreach($exec as $line)
				self::tick($line);
		}
	}
	
	public static function loop() {
		$interrupt = false;
		
		while(!$interrupt)
			$interrupt = self::tick();
	}
	
	protected static function tick($line = null) {
		if(!isset($line)) $line = Input::read(Evaluer::pathenize());
		$statement = self::$parser->parse($line);
		$result = Evaluer::execute($statement);
		
		Output::write($result);
		
		return $statement->interrupted();
	}
}