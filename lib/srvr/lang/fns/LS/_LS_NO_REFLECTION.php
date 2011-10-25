<?php
namespace AIP\lib\srvr\lang\fns\LS;

class _LS_NO_REFLECTION {
	public function init() {
		return new self;
	}
	
	public function __construct() {}
	
	public function render() {
		print_r(\AIP\lib\srvr\evlr\Evaluer::sandbox_vars());
	}
}