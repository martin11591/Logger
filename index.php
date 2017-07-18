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
    public const CRITICAL = 0b0000001;
    public const WARNING = 0b0000010;
    public const CONSTRUCTORS = 0b0000100;
    public const CALLING_METHODS = 0b0001000;
    public const GETTING_PROPERTIES = 0b0010000;
    public const SETTING_PROPERTIES = 0b0100000;
    public const DEBUG_INFO = 0b1000000;
    public static $mode = self::CRITICAL | self::WARNING | self::CONSTRUCTORS | self::CALLING_METHODS | self::GETTING_PROPERTIES | self::SETTING_PROPERTIES | self::DEBUG_INFO;

    /**
     * Return joined types as string
     *
     * @param int $bits
     * @return string
     */
    public static function getTypesNames($bits = self::DEBUG_INFO)
    {
        $arr = [];
        if ($bits & self::CRITICAL) {
            array_push($arr, 'CRITICAL');
        }
        if ($bits & self::WARNING) {
            array_push($arr, 'WARNING');
        }
        if ($bits & self::CONSTRUCTORS) {
            array_push($arr, 'CONSTRUCTORS');
        }
        if ($bits & self::CALLING_METHODS) {
            array_push($arr, 'CALLING_METHODS');
        }
        if ($bits & self::GETTING_PROPERTIES) {
            array_push($arr, 'GETTING_PROPERTIES');
        }
        if ($bits & self::SETTING_PROPERTIES) {
            array_push($arr, 'SETTING_PROPERTIES');
        }
        if ($bits & self::DEBUG_INFO) {
            array_push($arr, 'DEBUG_INFO');
        }
        return implode(' | ', $arr);
    }

    /**
     * Test passed type with current mode
     *
     * @param int $bits
     * @return boolean
     */
    public static function testTypes($bits = self::DEBUG_INFO) {
        return $bits & self::$mode;
    }
}

class Log {
    public static $date = false;
    public static $time = false;
    public static $getMilliSeconds = false;
    public static $asMicroSeconds = false;
    public static $handler = NULL;
    private static $internalLog = [];

    /**
     * Returns time format string based on current settings (date, milliseconds and microseconds)
     *
     * @return string
     */
    public static function getFormat() {
        return (self::$date === true ? "Y.m.d " : "") . "H:i:s" . (self::$getMilliSeconds === true ? "." . (self::$asMicroSeconds === false ? "v" : "u") : "");
    }

    /**
     * Returns current time formated by @getFormat() method
     *
     * @return string
     */
    public static function getCurrentTime() {
        $t = new DateTime('now');
        return $t->format(self::getFormat());
    }

    /**
     * Make new entry
     *
     * Current settings define how this will behave
     * It checks Log::$handler - if is NULL then it writes on screen
     *                           if is file handler then it writes to file
     *                           if is database handler then it store to database
     *
     * @param int $type
     * @param string $msg
     */
    public static function entry($type = Debug::DEBUG_INFO, $msg = '') {
        $eol = "<br/>" . PHP_EOL;
        $types = Debug::getTypesNames($type);
        if (Debug::testTypes($type) > 0) {
            if (gettype(self::$handler) === "resource") {
                self::entryToFile($type, $msg, self::$handler);
            }
            if (self::$handler === NULL) {
                echo "{$eol}{$types} : {$msg}";
            }
        }
    }

    public static function getInternalLog($separator = '<br/>' . PHP_EOL) {
        return implode($separator, self::$internalLog);
    }

    public static function clearInternalLog() {
        self::$internalLog = [];
    }

    public static function entryToInternal($type = Debug::DEBUG_INFO, $msg = '') {
        $types = Debug::getTypesNames($type);
        array_push(self::$internalLog, "{$types} : {$msg}");
    }

    public static function entryToFile($type = Debug::DEBUG_INFO, $msg = '', $handler = NULL) {
        if ($handler === NULL && gettype(self::$handler) !== 'resource') {
            return false;
        }
        if ($handler !== self::$handler && $handler === NULL) {
            $handler = self::$handler;
        }
        $eol = PHP_EOL;
        $types = Debug::getTypesNames($type);
        fwrite($handler, "{$eol}{$types} : {$msg}");
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
        if ($path !== '') {
            if ($create === true) {
                $this->createDir($this);
            } else {
                throw new PathException("Cannot create path \"{$path}\" - creating folders not allowed by parameter");
            }
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

    public function exist() {
        return file_exists($this->path);
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
        return substr(implode('/', array_map(function($arg) {
            return File::clean($arg);
        }, explode('/', $path))),1);
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
        if ($fileName !== '') {
            if (file_exists($fileName) === false && $create === false) {
                throw new FileException("File \"{$fileName}\" not exist and cannot be created by parameter");
            } else {
                $exist = $this->exist() === false ? "created" : "opened";
                $this->handler = fopen($this->getFullRelativePath(), $this->mode);
                if ($this->handler === false) {
                    throw new FileException("Error opening file \"{$fileName}\"");
                }
                Log::entryToInternal(Debug::DEBUG_INFO, "File \"{$fileName}\" {$exist}");
            }
        } else {
            throw new FileException("Filename not specified");
        }
        return $this;
    }

    public function getHandler()
    {
        return $this->handler;
    }

    public function exist() {
        return file_exists($this->getFullRelativePath());
    }

    public function getFullRelativePath() {
        return $this->path->getPath() . $this->getFileName();
    }

    public static function clean($fileName) {
        return rtrim(preg_replace('#<|>|:|"|\\\\|/|\||\*|\?#', '', $fileName), " .\t\n\r\0\x0B");
    }
}

set_error_handler(function($errno, $errstr, $errfile, $errline) {
   throw new ErrorException("Error " . $errno . "<br/>" . PHP_EOL . $errstr . " in file " . $errfile . " in line " . $errline);
}, E_ALL);

$file = new File("log.txt", true);

Log::entryToInternal(Debug::DEBUG_INFO, "TEST");

for ($i = 0; $i < 10; $i++) {
    Log::entryToInternal(Debug::DEBUG_INFO, "Message {$i}");
}

for ($i = 0; $i < 10; $i++) {
    Log::entryToInternal(Debug::DEBUG_INFO, "Message {$i}");
}

echo Log::getInternalLog();