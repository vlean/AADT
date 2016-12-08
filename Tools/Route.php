<?php
/**
 * Created by PhpStorm.
 * User: tk
 * Date: 16-10-25
 * Time: 下午11:39
 */

namespace Tools;



use \Exception;
use \ReflectionMethod;
use \Workerman\Protocols\HttpCache;

class Route
{
    public static $rules =[];
    public static $resoure = ['gif','jpg','png','css','htm','js','ico','less','ttf','eot','svg','woff'];
    static function set($key,$val){
        Route::$rules[$key]= $val;
    }
    static function parse_var($data){
        $_REQUEST = array_merge($data['get'],$data['post']);
        $_POST = $data['post'];
        $_GET  = $data['get'];
        $_FILES = $data['files'];
        $_SERVER = array_merge($_SERVER,$data['server']);
        $_COOKIE = array_merge($_COOKIE,$data['cookie']);
    }
    static function parse($uri){
        self::initLoad();
        $uri = ltrim($uri,'/');
        $info = pathinfo($uri);
        $extension = isset($info['extension']) && !empty($info['extension'])?$info['extension']:'html';
        HttpCache::$header['Content-Type']= self::resourceType($extension);
        if(isset($info['extension']) &&  in_array($info['extension'],Route::$resoure)){
            $path = APP_ROOT . DIRECTORY_SEPARATOR . $uri;
            if(!file_exists($path)) throw new Exception('file not found',404);
            return file_get_contents($path);
        }
        foreach(Route::$rules as $rule){

        }
        $uris = parse_url($uri);
        var_dump($uris);
        $uri = explode('/',$uris['path']);
        $class = APP_SITE."\\Controller\\";
        $class .= isset($uri[0]) && !empty($uri[0])?ucfirst($uri[0]):"Index";
        $class .= "Controller";
        if(!class_exists($class)) throw new Exception('class '. $class .' not exist',E_USER_WARNING);
        $obj = new $class;
        $method = isset($uri[1])?$uri[1]:'Index';
        if(!method_exists($obj,$method)) throw new Exception('function '.$method .' not exist',E_USER_WARNING);
        ob_start();
        $reflectionMethod = new ReflectionMethod($class, $method);
        $reflectionMethod->invoke($obj);
        $cont = ob_get_contents();
        ob_end_clean();
        return $cont;
    }

    static function initLoad(){
        if(is_file(APP_ROOT. "/".APP_SITE ."/Common/function.php")){
            require_once APP_ROOT. "/".APP_SITE ."/Common/function.php";
        }
        if(is_file(APP_ROOT. "/".APP_SITE ."/Common/config.php")){
            require_once APP_ROOT. "/".APP_SITE ."/Common/config.php";
        }
        if(is_file(APP_ROOT. "/".APP_SITE ."/Common/".APP_ENV .".php")){
            require_once APP_ROOT. "/".APP_SITE ."/Common/".APP_ENV .".php";
        }
    }

    static function resourceType($ext=''){
        switch($ext){
            case 'css':
                return "Content-Type: text/css";
                break;
            case 'png':
            case 'jpg':
            case 'jpeg':
            case 'gif':
                return "Content-Type: image/jpeg";
                break;
            case 'ttf':
                return "application/x-font-ttf";
                break;
            case 'woff':
            case 'svg':
            case 'eot':
            case 'otf':
                return "application/font-".$ext;
                break;
            case 'js':
                return "Content-Type: application/javascript";
                break;
            default:
                return "Content-Type: text/html;charset=utf-8";
                break;
        }

    }

}
