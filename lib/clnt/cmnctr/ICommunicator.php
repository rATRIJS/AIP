<?php
namespace AIP\lib\clnt\cmnctr;

interface ICommunicator {
	public static function available();
	public function send($input);
	public function get_statement();
	public function get_result();
	public function get_path();
}