<?php

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    throw new ErrorException("Error " . $errno . "<br/>" . PHP_EOL . $errstr . " in file " . $errfile . " in line " . $errline);
}, E_ALL);

function __autoload($className) {
    if (file_exists('controller/' . $className . '.php')) {
        require_once 'controller/' . $className .'.php';
        return true;
    } else {
        return false;
    }
}

require_once "view\index.php";