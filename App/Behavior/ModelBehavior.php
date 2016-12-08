<?php
/**
 * Created by PhpStorm.
 * User: tk
 * Date: 16-11-8
 * Time: 下午11:08
 */

namespace App\Behavior;
use \PDO;
trait ModelBehavior{
    function getDB(){
        $db = new PDO('mysql:host=127.0.0.1;dbname=api','root','');
        return $db;
    }
}