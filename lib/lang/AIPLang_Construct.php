<?php
namespace AIP\lib\lang;

abstract class AIPLang_Construct {
	abstract public static function parsable($line);
	abstract public static function parse($line);
}