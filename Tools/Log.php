<?php
/**
 * Created by PhpStorm.
 * User: tk
 * Date: 16-10-23
 * Time: 下午8:25
 */

namespace Tools;


class Log
{
    const INFO = 1;
    const DEBUG= 2;
    const WARNING=4;
    const ERROR = 8;

    const LEVEL = [
        self::INFO=>'INFO',
        self::DEBUG=>'DEBUG',
        self::WARNING=>'WARNING',
        self::ERROR=>'ERROR'
    ];

    public static $showLevel = self::INFO | self::DEBUG | self::ERROR | self::ERROR;

    public static $needLog = 0;

    public static function record($log, $level = 1){
        $log = is_string($log)?$log:json_encode($log,JSON_UNESCAPED_UNICODE);
        $info= sprintf("[%s][%s] %s\n",self::LEVEL[$level],date('H:i:s'),$log);
        if($level & Log::$showLevel){
           echo $info;
        }

        if(Log::$needLog){
            file_put_contents('/tmp/mp.log',$info,FILE_APPEND);
        }
    }
}