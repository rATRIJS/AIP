<?php
namespace AIP\lib\hlprs;

class Formatter {
	const NORMAL = 1;
	const BOLD = 2;
	const C_LIGHT_GRAY = 4;
	
	protected $text;
	protected $format;
	protected $map;
	
	public static function load($text, $format = 0) {
		return new self($text, $format);
	}
	
	public function __construct($text, $format = 0) {
		$this->text = $text;
		$this->format = $format;
		
		$this->map = array(
			self::NORMAL => "[0m",
			self::BOLD => "[1m",
			self::C_LIGHT_GRAY => "[1;37m"
		);
		
		$esc = chr(27);
		foreach($this->map as &$e) $e = $esc . $e;
	}
	
	public function bold() {
		$this->format = $this->format | self::BOLD;
		
		return $this;
	}
	
	public function c_light_gray() {
		$this->format = $this->format | self::C_LIGHT_GRAY;
		
		return $this;
	}
	
	public function format() {
		$text = $this->text;
		$has_style = false;
		foreach($this->map as $k => $v) {
			if($k !== ($k & $this->format)) continue;
			
			$text = $v . $text;
			$has_style = true;
		}
		
		if($has_style)
			$text .= $this->map[self::NORMAL];
		
		return $text;
	}
	
	public function __toString() {
		return $this->format();
	}
}