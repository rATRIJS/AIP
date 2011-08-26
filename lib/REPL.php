<?php
namespace AIP\lib;

class REPL {
	protected static $parser;
	
	public static function loop() {
		self::$parser = new Parser;
		
		$interrupt = false;
		
		while(!$interrupt)
			$interrupt = self::tick();
	}
	
	protected static function tick() {
		$line = Input::read(Evaluer::pathenize());
		$statement = self::$parser->parse($line);
		$result = Evaluer::execute($statement);
		
		Output::write($result);
		
		return $statement->interrupted();
	}
}