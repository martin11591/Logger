<?php

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
                //Log::entryToInternal(Debug::INFO, "File \"{$fileName}\" {$exist}");
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