<?php

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
    public const INFO = 0b1000000;
    public static $mode = self::CRITICAL | self::WARNING | self::CONSTRUCTORS | self::CALLING_METHODS | self::GETTING_PROPERTIES | self::SETTING_PROPERTIES | self::INFO;

    /**
     * Return joined types as string
     *
     * @param int $bits
     * @return string
     */
    public static function getTypesNames($bits = self::INFO)
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
        if ($bits & self::INFO) {
            array_push($arr, 'INFO');
        }
        return implode(' | ', $arr);
    }

    /**
     * Test passed type with current mode
     *
     * @param int $bits
     * @return boolean
     */
    public static function testTypes($bits = self::INFO) {
        return $bits & self::$mode;
    }
}