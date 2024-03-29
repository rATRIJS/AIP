<?php
namespace AIP;

define('_AIP_LIB_PATH', _AIP_PATH . '/lib');

/* <INITIALIZE LOADER> */
require _AIP_LIB_PATH . '/Loader.php';
lib\Loader::init();
/* </INITIALIZE LOADER> */

/* <RAW INIT INCLUDE> */
if(false !== lib\Config::get(lib\Config::OPTION_RAW_INIT_INCLUDE))
	include lib\Config::get(lib\Config::OPTION_RAW_INIT_INCLUDE);
/* </RAW INIT INCLUDE> */

/* <START REPL> */
lib\clnt\REPL::i()->loop();
/* </START REPL> */

// namespace {
// 	if(!function_exists('aipd')) {
// 		function aipd($vars) {
// 			return \AIP\lib\Debugger::debug($vars);
// 		}
// 	}
// }
// 
// namespace AIP {
// 	define('_AIP_LIB_PATH', _AIP_PATH . '/lib');
// 	require _AIP_LIB_PATH . '/Loader.php';
// 
// 	lib\Loader::init();
// 	//lib\Completor::init();
// 	
// 	if(false !== lib\Config::get(lib\Config::OPTION_BEFORE_REPL_INCLUDE))
// 		include lib\Config::get(lib\Config::OPTION_BEFORE_REPL_INCLUDE);
// 
// 	lib\REPL::i()->loop();
// }