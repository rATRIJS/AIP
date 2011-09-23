<?php
namespace AIP\lib\lang\fns;

class AIPLang_Function_HISTORY extends AIPLang_Function {
	protected $args;
	
	public static function parsable($line, $statement) {
		return $line === 'history' or substr($line, 0, 8) === 'history ' or preg_match('#^!\d+$#', $line);
	}
	
	public static function parse($line, $statement) {
		if(substr($line, 0, 1) === '!') {
			$id = (int) substr($line, 1);
			
			return \AIP\lib\Input::history_id($id);
		}
		
		$line = explode(' ', $line, 2);
		
		if(!isset($line[1])) $line[1] = '';
		
		return '\\AIP\\lib\\lang\\fns\\AIPLang_Function_HISTORY::execute(\'' . $line[1] . '\')';
	}
	
	public static function execute($args = '') {
		$h = new self($args);
		$h->history();
		
		return \AIP\lib\hlprs\NotReturnable::i();
	}
	
	public function __construct($args) {
		$this->args = $this->_parse_args($args);
	}
	
	public function history() {
		extract($this->args);
		
		\AIP\lib\Evaluer::make_internal_from(
			\AIP\lib\Evaluer::SOURCE_OUTPUT,
			'History'
		);
		
		$history = \AIP\lib\Input::history($length, $start);
		$history = array_reverse($history, true);
		
		$data = array();
		foreach($history as $id => $line)
			$data[] = "\t{$id}\t{$line}";
			
		echo "Use !{HISTORY_ID} to re-invoke history line.\n\n" . implode("\n", $data);
	}
	
	protected function _parse_args($args) {
		$args = \AIP\lib\Optionizer::init($args, array(
			'start' => array(
				'keys' => array('s', 'start'),
				'supports_value' => true
			),
			'length' => array(
				'keys' => array('l', 'length'),
				'supports_value' => true
			)
		))->parse();
		
		if(!isset($args['start'])) $args['start'] = 1;
		if(!isset($args['length'])) $args['length'] = 10;
		
		$args['start'] = (int) $args['start'];
		$args['length'] = (int) $args['length'];
		
		return $args;
	}
}