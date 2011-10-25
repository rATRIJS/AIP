<?php
namespace AIP\lib\clnt\cmnctr;

class ClientServerCommunicator implements ICommunicator {
	public static function available() {
		return function_exists('socket_create') and false;
	}
	
	public function send($input) {
		//
	}
	
	public function get_statement() {
		//
	}
	
	public function get_result() {
		//
	}
	
	public function get_path() {
		//
	}
	
	public function get_last_history() {
		//
	}
}