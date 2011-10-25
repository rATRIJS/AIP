<?php
namespace AIP\lib\srvr\lang\fns\LS;

class _LS_METHOD {
	protected $_reflection;
	protected $_args;
	
	public function init(\ReflectionMethod $reflection, $args) {
		return new self($reflection, $args);
	}
	
	public function __construct(\ReflectionMethod $reflection, $args) {
		$this->_reflection = $reflection;
		$this->_args = $this->_parse_args($args);
	}
	
	public function render() {
		extract($this->_args);
		
		$parameters = $this->_reflection->getParameters();
		
		$output = "Arguments:\n";
		foreach($parameters as $parameter) {
			$is_optional = $parameter->isOptional();
			$is_required = !$is_optional;
			$is_passed_by_reference = $parameter->isPassedByReference();
			
			if($is_optional and !in_array('optional', $types)) continue;
			if($is_required and !in_array('required', $types)) continue;
			if($is_passed_by_reference and !in_array('by_reference', $types)) continue;
			
			$output .= "\t- ";
			if($is_passed_by_reference) $return .= '&';
			$output .= '$' . $parameter->getName() . ' ';
			if($is_optional) $return .= '= ' . str_replace("\n", '', var_export($parameter->getDefaultValue(), true));
			$output .= "\n";
		}
		
		echo $output;
	}
	
	protected function _parse_args($args) {
		return array(
			'types' => array(
				'optional',
				'required',
				'by_reference'
			)
		);
	}
}