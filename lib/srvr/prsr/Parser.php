<?php
namespace AIP\lib\srvr\prsr;

class Parser {
	protected static $_i;
	
	protected $statement;
	
	public static function i() {
		if(!isset(self::$_i)) self::$_i = new self;
		
		return self::$_i;
	}
	
	protected function __construct() {		
		$this->statement = new Statement;
	}
	
	public function parse($line) {
		$this->statement->set_aip($line);
		
		$line = Helper::sanitize($line);
		
		$starts_block = Helper::starts_block($line);
		$ends_block = Helper::ends_block($line);
		
		if($starts_block) $this->statement->increase_block_level();
		if($ends_block) $this->statement->decrease_block_level();
		
		$line = Aipenizer::i()->run($line, $this->statement);
		
		if(!$starts_block and !$ends_block)
			$line = Helper::semicolonize($line);
		
		$this->statement->interrupted(Helper::interrupts($line));
		if($this->statement->interrupted()) $line = Helper::aip_die();
		
		$this->statement->append($line);
			
		$statement = clone $this->statement;
		
		if(!$this->statement->in_block())
			$this->statement = new Statement;
			
		return $statement;
	}
}