<?php
/**
 * Created by PhpStorm.
 * User: vlean
 * Date: 2016/10/31
 * Time: 下午5:20
 */

namespace Tools;


class RulePaser
{
    public static $rule = [];
    private static $split = [" ","\t","/"];

    static function load($str){
        self::$rule = [];
        $strs = explode("\n",$str);
        foreach($strs as $key=>$str){
            if(empty($str))
                unset($strs[$key]);
            else
                self::parseDetail($str);
        }
        return self::$rule;
    }
    static function parseDetail($str)
    {
        $strs = self::mbStringToArray($str);
        $info = ["key" => '', "type" => '', "default" => "", "level" => 0, "must" => 1, "length" => "", "size" => "", "remark" => ""];
        $flag = 0; //0:层级 1:key 2:备注 3:type 4:length 5:size 6:must
        $size_flag = 0;
        foreach ($strs as $char) {
            switch ($flag) {
                case 0:
                    if ($char == "\t" || $char == " ")
                        $info["level"]++;
                    else {
                        $flag = 1;
                        $info["key"] = $char;
                    }
                    break;
                case 1:
                    if ($char == ":") {
                        $flag = 2;
                    } elseif (in_array($char, self::$split)) {
                        $flag = 3;
                    } else {
                        $info["key"] .= $char;
                    }
                    break;
                case 2:
                    if (in_array($char, self::$split)) {
                        $flag = 3;
                    } else {
                        $info["remark"] .= $char;
                    }
                    break;
                case 3:
                    if ($char == "(") {
                        $flag = 4;
                    } elseif (in_array($char, self::$split)) {
                        $flag = 5;
                    } elseif ($char != ")") {
                        $info['type'] .= $char;
                    }
                    break;
                case 4:
                    if (in_array($char, self::$split)) {
                        $flag = 5;
                    } elseif ($char != ")") {
                        $info['length'] .= $char;
                    }
                    break;
                default:
                    //size
                    if($size_flag == 0 && $flag==5 && (strpos("0123456789",$char)!=-1 || (!empty($info['size']) && ($char=='-'|| $char==',') ) ) && ($char!="N" || $char!="Y")){
                        $info['size'] .= $char;
                    }
                    if($flag==5 && !empty($info['size']) && ($char == " "|| $char=="/")){
                        $size_flag = 1;
                        $flag = 6;
                    }
                    //Y
                    if ($char == "Y") {
                        $info['must'] = 1;
                    } elseif ($char == "N") {
                        $info['must'] = 0;
                    }
                    break;
            }

        }
        if(!empty($info["key"])){
            $info['length'] +=0;
            self::$rule[] = $info;
        }
    }

    static function mbStringToArray ($string) {
        $strlen = mb_strlen($string);
        $array = [];
        while ($strlen) {
            $array[] = mb_substr($string,0,1,"UTF-8");
            $string = mb_substr($string,1,$strlen,"UTF-8");
            $strlen = mb_strlen($string);
        }
        return $array;
    }
}

