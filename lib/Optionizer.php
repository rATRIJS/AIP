<?php
namespace AIP\lib;

use \AIP\excptns\lib as E;

class Optionizer {
	public $opt_str;
	public $supported_options;
	
	public function init($opt_str, $supported_options) {
		return new self($opt_str, $supported_options);
	}
	
	public function __construct($opt_str, $supported_options) {
		$this->opt_str = $opt_str;
		$this->supported_options = $supported_options;
	}
	
	public function parse() {
		$this->validate();
		
		$options = explode(' ', $this->opt_str);
		
		$parsed_options = array();
		foreach($this->supported_options as $name => $supported_option) {
			$this->_defaultize_supported_option($name, $supported_option);
			
			foreach($options as $i => $v) {
				if(substr($v, 0, 1) !== '-') continue;
				
				$key = $this->_extract_key($v);
				
				if(!in_array($key, $supported_option['keys'])) continue;
				
				if($supported_option['supports_value']) {
					$value_exists = (isset($options[$i + 1]) and substr($options[$i + 1], 0, 1) !== '-');
					
					if(!$supported_option['optional'] and !$value_exists)
						throw new E\AIPOptionizer_InvalidOptionException("Option '{$name}' needs a value.");
						
					$parsed_options[$name] = $value_exists ? $options[$i + 1] : $supported_option['default'];
				}
				else {
					$parsed_options[$name] = true;
				}
			}
		}
		
		return $parsed_options;
	}
	
	public function validate() {
		if(!is_string($this->opt_str))
			throw new E\AIPOptionizer_InvalidOptionStringException("Option string must be a string. '" . gettype($this->opt_str) . "' given.");
			
		if(!is_array($this->supported_options) or empty($this->supported_options))
			throw new E\AIPOptionizer_InvalidSupportedOptionsException("Supported options must be a non-empty array.");
			
		return $this;
	}
	
	protected function _extract_key($key) {
		return substr($key, 0, 2) === '--' ? substr($key, 2) : substr($key, 1);
	}
	
	protected function _defaultize_supported_option($name, &$supported_option) {
		if(!is_array($supported_option) or empty($supported_option['keys']))
			throw new E\AIPOptionizer_InvalidSupportedOptionException("Supported option must be an array and must have 'keys' key set.");
			
		$supported_option = array_merge(array(
			'optional' => false,
			'supports_value' => false,
			'default' => false,
			'name' => $name
		), $supported_option);
		
		return $this;
	}
}