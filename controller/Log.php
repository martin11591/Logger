<?php

class Log {
    public $date = false;
    public $time = false;
    public $getMilliSeconds = false;
    public $asMicroSeconds = false;
    protected $handler = NULL;
    private $internalLog = [];

    public function __construct($date = false, $time = false, $getMilliSeconds = false, $asMicroSeconds = false) {
        $this->date = $date;
        $this->time = $time;
        $this->getMilliSeconds = $getMilliSeconds;
        $this->asMicroSeconds = $asMicroSeconds;
    }

    /**
     * Returns time format string based on current settings (date, milliseconds and microseconds)
     *
     * @return string
     */
    public function getFormat() {
        return ($this->date === true ? "Y.m.d " : "") . "H:i:s" . ($this->getMilliSeconds === true ? "." . ($this->asMicroSeconds === false ? "v" : "u") : "");
    }

    /**
     * Returns current time formated by @getFormat() method
     *
     * @return string
     */
    public function getCurrentTime() {
        $t = new DateTime('now');
        return $t->format($this->getFormat());
    }

    public function getInternalLog($separator = '<br/>' . PHP_EOL) {
        return implode($separator, $this->internalLog);
    }

    public function clearAllInternalLog() {
        $this->internalLog = [];
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
    public function entry($type = Debug::INFO, $msg = '') {
        $eol = "<br/>" . PHP_EOL;
        $types = Debug::getTypesNames($type);
        if (Debug::testTypes($type) > 0) {
            if (gettype($this->handler) === "resource") {
                $this->entryToFile($type, $msg, $this->handler);
            }
            if ($this->handler === NULL) {
                echo "{$eol}{$types} : {$msg}";
            }
        }
    }

    public function entryToInternal($type = Debug::INFO, $msg = '') {
        $types = Debug::getTypesNames($type);
        array_push($this->internalLog, "{$types} : {$msg}");
    }

    public function entryToFile($type = Debug::INFO, $msg = '', $handler = NULL) {
        if ($handler === NULL && gettype($this->handler) !== 'resource') {
            return false;
        }
        if ($handler !== $this->handler && $handler === NULL) {
            $handler = $this->handler;
        }
        $eol = PHP_EOL;
        $types = Debug::getTypesNames($type);
        fwrite($handler, "{$eol}{$types} : {$msg}");
    }
}