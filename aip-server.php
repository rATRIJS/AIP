<?php
namespace AIP;

define('_AIP_PATH', dirname(__FILE__));
define('_AIP_LIB_PATH', _AIP_PATH . '/lib');

/* <INITIALIZE LOADER> */
require _AIP_LIB_PATH . '/Loader.php';
lib\Loader::init();
/* </INITIALIZE LOADER> */

lib\srvr\cmnctn\Controller::i()->repl();