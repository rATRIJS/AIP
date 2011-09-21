<?php
namespace AIP\lib\hlprs;

class NotReturnable {
	public static function i() {
		return new self;
	}
}