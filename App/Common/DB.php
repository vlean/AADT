<?php

/**
 * Created by PhpStorm.
 * User: tk
 * Date: 16-11-8
 * Time: 下午11:11
 */

namespace App\Common;

use \MysqliDb;
class DB
{
    private static $instance = null;
    private function __construct(){

    }

    private function __clone(){

    }

    public static function getInstance(){
        if(!empty(self::$instance)){
            return self::$instance;
        }

        self::$instance = new MysqliDb (DB_HOST, DB_USER, DB_PWD, DB_NAME);
        return self::$instance;
    }
}
