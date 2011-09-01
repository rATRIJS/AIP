<?php
namespace AIP\lib;

class Parser {
	protected $statement;
	protected $aip_lang_constructs;
	
	public function __construct() {		
		$this->statement = new Statement;
		$this->aip_lang_constructs = Config::get(Config::OPTION_AIP_LANG_CONSTRUCTS);
	}
	
	public function parse($line) {
		$line = $this->sanitize($line);
		
		$starts_block = $this->starts_block($line);
		$ends_block = $this->ends_block($line);
		
		$line = $this->aip_to_php($line);
		
		if(!$starts_block and !$ends_block)
			$line = $this->semicolonize($line);
			
		if($starts_block) $this->statement->increase_block_level();
		if($ends_block) $this->statement->decrease_block_level();
		$this->statement
			->append($line)
			->interrupted($this->interrupts($line));
			
		$statement = clone $this->statement;
		
		if(!$this->statement->in_block())
			$this->statement = new Statement;
			
		return $statement;
	}
	
	protected function starts_block($line) {
		return in_array(substr($line, -1), array('{', ':'));
	}
	
	protected function ends_block($line) {
		return substr($line, -1) === '}';
	}
	
	protected function aip_to_php($line) {
		foreach($this->aip_lang_constructs as $construct)
			if($construct::parsable($line))
				$line = $construct::parse($line);
		
		return $line;
	}
	
	protected function semicolonize($line) {
		if(substr($line, -1) !== ';')
			$line .= ';';
		
		return $line;
	}
	
	protected function interrupts($line) {
		$valid_interrupts = array(
			'die;',
			'die();',
			'exit;',
			'exit();'
		);
		
		return in_array($line, $valid_interrupts);
	}
	
	protected function sanitize($line) {
		$line = trim($line);
		
		return $line;
	}
}