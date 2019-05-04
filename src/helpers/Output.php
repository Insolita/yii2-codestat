<?php
/**
 * Created by solly [18.10.17 7:15]
 */

namespace insolita\codestat\helpers;

use yii\helpers\BaseConsole;

class Output extends BaseConsole
{
    /**
     * @param array $data
     * @param bool  $withIndex
     */
    public static function arrayList(array $data, $withIndex = true)
    {
        foreach ($data as $index => $line) {
            if ($withIndex) {
                self::stdout(' ' . $index . ' - ');
            }
            self::output($line);
        }
    }
    
    
    public static function separator($string = '-', $multiplier = 25)
    {
        self::output(str_repeat($string, $multiplier));
    }
    
    public static function info($string)
    {
        self::output(self::ansiFormat($string, [self::FG_CYAN]));
    }
    
    public static function success($string)
    {
        self::output(self::ansiFormat($string, [self::FG_GREEN]));
    }
    
    public static function warn($string)
    {
        self::output(self::ansiFormat($string, [self::FG_PURPLE]));
    }
}
