<?php
namespace AIP\lib;

class Loader {
	public static function init() {
		spl_autoload_register(array('\\' . __CLASS__, 'load'));
	}
	
	public static function load($class) {
		if(substr($class, 0, 4) !== 'AIP\\') return;
		
		$path = explode('\\', $class);
		$class = array_pop($path);
		array_shift($path);
		
		$path = empty($path) ? '' : (implode('/', $path) . '/');
		$path = _AIP_PATH . '/' . $path . "{$class}.php";
		
		if(file_exists($path))
			require $path;
	}
}