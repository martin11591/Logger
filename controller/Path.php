<?php

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