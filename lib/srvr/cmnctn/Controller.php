<?php
namespace AIP\lib\srvr\cmnctn;

use \AIP\excptns\lib\srvr\cmnctn\cntrllr as E;
use \AIP\lib\clnt\Output;

class Controller {
	protected static $_i;
	
	protected $_master_socket;
	protected $_connections;
	
	public static function i() {
		if(!isset(self::$_i)) self::$_i = new self;
		
		return self::$_i;
	}
	
	protected function __construct() {
		$this->_connections = array();
		
		$this->_create_master_socket();
	}
	
	public function repl() {
		if(false === socket_listen($this->_master_socket))
			throw $this->_get_socket_exception("Couldn't listen for master socket.");
		
		while(true) {
			$sockets = $this->_get_connected_sockets();
			
			$this->_log('select()');
			$modified = socket_select($sockets, $tmp = null, $tmp = null, null);
			if(false === $modified)
				throw $this->_get_socket_exception("Couldn't execute select() system call.");
			
			if($this->_socket_modified($this->_master_socket, $sockets))
				$this->_accept_connection();
			
			$this->_handle_writes($sockets);
		}
	}
	
	protected function _handle_writes($modified_sockets) {
		foreach($this->_connections as $k => &$connection) {
			$socket = $connection->socket();
			
			if(!$this->_socket_modified($socket, $modified_sockets)) continue;
			
			if(false === socket_set_nonblock($socket))
				throw $this->_get_socket_exception("Couldn't set NON blocking mode.");
			
			$input = '';
			do {
				$this->_log('read({REMOTE_DETAILS})', $connection);
				$buffer = socket_read($socket, 1048576, PHP_NORMAL_READ); // read 1Mb
				$buffer = $this->_sanitize_read($buffer);
				
				if(false === $buffer) {
					$this->_close_connection($k, $connection);
					$input = '';
					break;
				}
				
				if(true !== $buffer) $input .= $buffer;
			}
			while(true !== $buffer);
			
			if(false === socket_set_block($socket))
				throw $this->_get_socket_exception("Couldn't set blocking mode.");
			
			if('' !== $input) {
				$this->_log("read({REMOTE_DETAILS}) = \"{$input}\"", $connection);
			}
		}
	}
	
	protected function _close_connection($connection_key, $connection) {
		$this->_log("close({REMOTE_DETAILS})", $connection);
		socket_close($connection->socket());
		unset($this->_connections[$connection_key]);
		
		return $this;
	}
	
	protected function _accept_connection() {
		$connection = socket_accept($this->_master_socket);
		if(false === $connection)
			throw $this->_get_socket_exception("Couldn't accept connection.");
		
		$connection = new Connection($connection);
		$this->_connections[] = $connection;
		
		$this->_log("accept({REMOTE_DETAILS})", $connection);
		
		return $this;
	}
	
	protected function _socket_modified(&$socket, &$modified_sockets) {
		return in_array($socket, $modified_sockets);
	}
	
	protected function _get_connected_sockets() {
		$sockets = array($this->_master_socket);
		
		foreach($this->_connections as &$connection)
			$sockets[] = $connection->socket();
		
		return $sockets;
	}
	
	protected function _create_master_socket() {
		$this->_master_socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
		
		if(false === $this->_master_socket)
			throw $this->_get_socket_exception("Couldn't create master socket.");
		
		extract($this->_parse_addr(\AIP\lib\Config::get(\AIP\lib\Config::OPTION_SERVER_ADDR)));
		
		if(false === socket_bind($this->_master_socket, $host, $port))
			throw $this->_get_socket_exception("Couldn't bind master socket to address.");
		
		return $this;
	}
	
	protected function _sanitize_read($read) {
		$read = str_replace("\r", "\n", $read);
		while(false !== strpos($read, "\n\n")) $read = str_replace("\n\n", "\n", $read);
		
		if(strlen($read) > 1) $read = trim($read);
		
		if(in_array($read, array('', 'quit'))) return false;
		elseif("\n" === $read) return true;
		else return $read;
	}
	
	protected function _parse_addr($addr) {
		$addr = explode(':', $addr, 2);
		
		if(!isset($addr[1]))
			throw new E\SocketAddrException("Invalid address given. Address must follow format '{HOST}:{PORT}'.");
		
		return array('host' => $addr[0], 'port' => $addr[1]);
	}
	
	protected function _get_connection_id(Connection $connection) {
		foreach($this->_connections as $k => &$c)
			if($c === $connection) return $k;
		
		throw new E\ConnectionNotFoundException("Given connection wasn't found in registered connections.");
	}
	
	protected function _log($message, Connection $connection = null) {
		if(isset($connection)) {
			$message = str_replace('{REMOTE_DETAILS}', $this->_get_remote_details($connection), $message);
		}
		
		Output::raw_write(@date('Ymd@His') . "\t" . $message);
	}
	
	protected function _get_remote_details(Connection $connection) {
		if(false === socket_getpeername($connection->socket(), $address, $port))
			throw $this->_get_socket_exception("Couldn't get remote details.");
		
		$connection_id = $this->_get_connection_id($connection);
		
		return "{$address}:{$port}[{$connection_id}]";
	}
	
	protected function _get_socket_exception($message = 'Socket error.') {
		$err_code = socket_last_error();
		$err_str = socket_strerror($err_code);
		
		return new E\SocketException("{$message} Got error #{$err_code}: '{$err_str}'");
	}
}