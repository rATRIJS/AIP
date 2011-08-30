<?php
namespace AIP;

define('_AIP_LIB_PATH', _AIP_PATH . '/lib');
require _AIP_LIB_PATH . '/Loader.php';

\AIP\lib\Loader::init();
\AIP\lib\Completor::init();

\AIP\lib\REPL::init();
\AIP\lib\REPL::loop();