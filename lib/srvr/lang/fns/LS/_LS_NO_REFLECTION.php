<?php
namespace AIP\lib\lang\fns\LS;

class _LS_NO_REFLECTION {
	public function init() {
		return new self;
	}
	
	public function __construct() {}
	
	public function render() {
		print_r(\AIP\lib\Evaluer::sandbox_vars());
	}
}