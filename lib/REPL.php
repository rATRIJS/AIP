<?php
namespace AIP\lib;

class REPL {
	protected static $i;
	
	protected $parser;
	
	public static function i() {
		if(!isset(self::$i)) self::$i = new self;
		
		return self::$i;
	}
	
	protected function __construct() {
		$this->parser = new Parser;
	}
	
	public function loop() {
		$this->run_before_loop_exec();
		
		$interrupt = false;
		while(!$interrupt)
			$interrupt = $this->tick();
	}
	
	public function tick($line = null) {
		if(!isset($line)) $line = Input::read(Evaluer::pathenize());
		$statement = $this->parser->parse($line);
		$result = Evaluer::execute($statement);
		
		Output::write($result);
		
		return $statement->interrupted();
	}
	
	protected function run_before_loop_exec() {
		$exec = Config::get(Config::OPTION_BEFORE_LOOP_EXEC);
		
		ob_start();
		foreach($exec as $line)
			$this->tick($line);
		$output = ob_get_clean();
		
		if(Config::get(Config::OPTION_VERBOSITY) > 0) {
			Output::write(Result::message("Start of 'before_loop_exec'"));
			Output::raw_write($output);
			Output::write(Result::message("End of 'before_loop_exec'"));
		}
	}
}