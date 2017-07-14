<?php

class DebugException extends Exception {}
class LogException extends Exception {}
class PathException extends Exception {}
class FileException extends Exception {}

class Debug {
    // Bits: G F E D C B A
    // A - Critical
    // B - Warning
    // C - Constructing class
    // D - Calling methods
    // E - Getting properties
    // F - Setting properties
    // G - Debug info
    public const NONE = 0;
    public const CRITICAL = 0b0000001;
    public const WARNING = 0b0000010;
    public const CONSTRUCTORS = 0b0000100;
    public const CALLING_METHODS = 0b0001000;
    public const GETTING_PROPERTIES = 0b0010000;
    public const SETTING_PROPERTIES = 0b0100000;
    public const DEBUG_INFO = 0b1000000;
    public static $mode = self::CRITICAL | self::WARNING | self::CONSTRUCTORS | self::CALLING_METHODS | self::GETTING_PROPERTIES | self::SETTING_PROPERTIES | self::DEBUG_INFO;

    public static function getTypesNames($bits = 0b1000000) {
        $arr = [];
        if ($bits & self::CRITICAL) array_push($arr, 'Debug::CRITICAL');
        if ($bits & self::WARNING) array_push($arr, 'Debug::WARNING');
        if ($bits & self::CONSTRUCTORS) array_push($arr, 'Debug::CONSTRUCTORS');
        if ($bits & self::CALLING_METHODS) array_push($arr, 'Debug::CALLING_METHODS');
        if ($bits & self::GETTING_PROPERTIES) array_push($arr, 'Debug::GETTING_PROPERTIES');
        if ($bits & self::SETTING_PROPERTIES) array_push($arr, 'Debug::SETTING_PROPERTIES');
        if ($bits & self::DEBUG_INFO) array_push($arr, 'Debug::DEBUG_INFO');
        return implode(' | ', $arr);
    }

    public static function testTypes($bits = 0b1000000) {
        return $bits & self::$mode;
    }
}

class Log {
    public static $date = false;
    public static $time = false;
    public static $getMilliSeconds = false;
    public static $asMicroSeconds = false;
    public static $handler = NULL;

    public static function getFormat() {
        return (self::$date === true ? "Y.m.d " : "") . "H:i:s" . (self::$getMilliSeconds === true ? "." . (self::$asMicroSeconds === false ? "v" : "u") : "");
    }

    public static function getCurrentTime() {
        $t = new DateTime('now');
        return $t->format(self::getFormat());
    }

    public static function entry($type = Debug::DEBUG_INFO, $msg = '') {
        if (Debug::testTypes($type) > 0) {
            echo Debug::getTypesNames($type) . " : LOG" . PHP_EOL;
        }
    }
}

class Path {
    protected $enteredPath;
    protected $path;
    protected $pathArray;
    protected $currentLevel;
    protected $maxLevel;

    public function __construct($path = '', $create = false) {
        $this->setPath($path, $create);
        return $this;
    }

    public function getPath() {
        return $this->path;
    }

    protected function setPath($path, $create = false) {
        $this->enteredPath = $path;
        $path = self::clean($path . '/');
        $this->pathArray = explode('/', $path);
        $this->maxLevel = $this->currentLevel = count($this->pathArray) - 1;
        $this->path = $path;
        if ($path !== '' && $create === true) {
            $this->createDir($this);
        } else {
            throw new PathException('Path "' . $path . '" not exists and creating directories are not allowed');
        }
        return $this;
    }

    public function getEnteredPath() {
        return $this->enteredPath;
    }

    public function getCurrentLevel() {
        return $this->currentLevel;
    }

    public function getMaxLevel() {
        return $this->maxLevel;
    }

    public static function createDir(Path $path) {
        try {
            mkdir($path->getPath(), 0777, true);
        } catch (Exception $e) {
            throw new PathException($e);
        }
    }

    public static function clean($path) {
        $path = preg_replace('<(\\\\|/)+>', '/', $path);
        return implode('/', array_map(function($arg) {
            return File::clean($arg);
        }, explode('/', $path)));
    }
}

class File {
    private $path;
    private $fileName;
    private $mode;
    private $handler;

    public function __construct($fileName = "log.txt", $create = true, $mode = 'a') {
        $this->mode = $mode . 'b';
        $fileNameArray = explode('/', $fileName);
        $path = implode('/', array_slice($fileNameArray, 0, -1));
        $this->path = new Path($path, $create);
        $fileName = self::clean(array_slice($fileNameArray, -1, 1)[0]);
        $this->setFileName($fileName, $create);
        return $this;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function setFileName($fileName, $create) {
        $this->fileName = self::clean($fileName);
        if (!file_exists($fileName) && $fileName !== '' && $create === true) {
            throw new FileException('File "' . $fileName . '" not exists and creating files are not allowed');
        }
        try {
            $this->handler = fopen($this->getFullRelativePath(), $this->mode);
        } catch(Exception $e) {
            throw new FileException($e);
        }
        return $this;
    }

    public function getFullRelativePath() {
        return $this->path->getPath() . $this->getFileName();
    }

    public static function clean($fileName) {
        return rtrim(preg_replace('#<|>|:|"|\\\\|/|\||\*|\?#', '', $fileName), " .\t\n\r\0\x0B");
    }
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
   throw new ErrorException("Error " . $errno . "<br/>" . $errstr . " in file " . $errfile . " in line " . $errline);
}, E_ALL);

echo "<br/>" . Path::clean("fdsfds/fdfds");
echo "<br/>" . Debug::getTypesNames(Debug::$mode);
echo "<br/>DEBUG_WARNING in mode: " . Debug::getTypesNames(Debug::testTypes(Debug::WARNING | Debug::DEBUG_INFO));
Debug::$mode = 0b1010101;
echo "<br/>DEBUG_WARNING in mode: " . Debug::getTypesNames(Debug::testTypes(Debug::WARNING));
if (Debug::testTypes(Debug::WARNING) === 0) echo "<br/>TRUE";
echo "<br/>" . Debug::getTypesNames(Debug::$mode);
echo "<br/>" . decbin(Debug::$mode & (Debug::CONSTRUCTORS | Debug::WARNING));
echo "<br/>" . decbin(Debug::CONSTRUCTORS | Debug::WARNING);
die();

$file = new File("log.txt", true);

//echo $path->getEnteredPath(). '<br/>' . $path->getPath() . '<br/>' . $path->getMaxLevel();
//echo "<br/>".$file->getFullRelativePath();