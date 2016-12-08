<?php

/**
 * Created by PhpStorm.
 * User: tk
 * Date: 16-10-25
 * Time: 下午11:55
 */

namespace App\Controller;
class IndexController
{
    private static $_vars = array();

    function index(){
        echo  'welcome :)';exit();
    }

    protected function display($tpl=''){
        $debug = debug_backtrace();
        $class = $debug[1]['class'];
        $class = explode("\\",$class);
        $class = end($class);
        $class = substr($class,0,-10);
        $method = $debug[1]['function'];

        if($tpl){
            $tpls = explode(':',$tpl);
            if(count($tpls) == 2){
                $class = $tpls[0];
                $method = $tpls[1];
            }else{
                $method = end($tpls);
            }
        }
        foreach(IndexController::$_vars as $key=>$val){
            $$key = $val;
        }
        require APP_ROOT ."/".APP_SITE ."/View/{$class}/{$method}.html";
    }

    protected function assign($key,$val){
        IndexController::$_vars[$key] = $val;
    }

    public function ajaxReturn($code=0,$msg='succ',$data=''){
        $data = [
            'errno'=>$code,
            'data'=>$data,
            'msg'=>$msg
        ];
        echo json_encode($data);
    }

    public function redirect($url,$msg='跳转中...',$waittime=3){
        $url = substr($url,0,7) == 'http://'?$url: 'http://'.$_SERVER['SERVER_NAME'] .'/'.$url;
        $this->assign('url',$url);
        $this->assign('msg',$msg);
        $this->assign('waittime',$waittime);
        $this->display('Common:redirect');
    }

    public function success($waittime=3){
        $url = $_SERVER['HTTP_REFERER'];
        $this->redirect($url,':)',$waittime);
    }

    public function error($waittime=3){
        $url = $_SERVER['HTTP_REFERER'];
        $this->redirect($url,':(',$waittime);
    }
}
