<?php
namespace AIP\lib\clnt;

class REPL {
	protected static $i;
	
	public static function i() {
		if(!isset(self::$i)) self::$i = new self;
		
		return self::$i;
	}
	
	protected function __construct() {}
	
	public function loop() {
		$this->run_before_loop_exec();
		
		$interrupt = false;
		while(!$interrupt)
			$interrupt = $this->tick();
	}
	
	public function tick($line = null) {
		if(!isset($line)) $line = Input::read(cmnctr\Communicator::i()->get_path());
		
		cmnctr\Communicator::i()->send($line);
		Output::write(cmnctr\Communicator::i()->retrieve());
		
		return cmnctr\Communicator::i()->is_interrupted();
	}
	
	protected function run_before_loop_exec() {
		$exec = \AIP\lib\Config::get(\AIP\lib\Config::OPTION_BEFORE_LOOP_EXEC);
		
		ob_start();
		foreach($exec as $line)
			$this->tick($line);
		$output = ob_get_clean();
		
		if(\AIP\lib\Config::get(\AIP\lib\Config::OPTION_VERBOSITY) > 0) {
			Output::write(\AIP\lib\srvr\evlr\Result::message("Start of 'before_loop_exec'"));
			Output::raw_write($output);
			Output::write(\AIP\lib\srvr\evlr\Result::message("End of 'before_loop_exec'"));
		}
	}
}