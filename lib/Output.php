<?php
namespace AIP\lib;

class Output {
	public static function write(Result $result) {
		echo $result->render() . "\n";
	}
}