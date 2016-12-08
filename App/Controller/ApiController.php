<?php
/**
 * Created by PhpStorm.
 * User: vlean
 * Date: 2016/10/28
 * Time: 下午5:31
 */

namespace App\Controller;

use App\Common\Model;
use Tools\RulePaser;

class ApiController extends IndexController
{
    function edit(){
        $id = isset($_REQUEST['id'])?$_REQUEST['id']:0;
        $fid = isset($_REQUEST['fid'])?$_REQUEST['fid']:0;
        $this->assign('id',$id);
        $this->assign('fid',$fid);
        $this->display();
    }

    function parses(){
        $data['title'] = $_REQUEST['title'];
        $data['url'] = $_REQUEST['url'];
        $data['desc'] = $_REQUEST['desc'];
        $data['version'] = $_REQUEST['version'];
        $data['fid'] = $_REQUEST['fid'];
        $data['id'] = $_REQUEST['id'];
        $resp = $err = $params = [];
        if(isset($_REQUEST['params']['keys'])){
            foreach($_REQUEST['params']['keys'] as $key=>$filed){
                if(empty($filed)) continue;
                $tmp = $_REQUEST['params'];
                $tmp_params['key'] = $filed;
                $tmp_params['must'] = $tmp['musts'][$key]+0;
                $tmp_params['default'] = $tmp['defaults'][$key];
                $tmp_params['type'] = $tmp['types'][$key];
                $tmp_params['rule'] = $tmp['rules'][$key];
                $tmp_params['name'] = $tmp['names'][$key];
                $tmp_params['remark'] = $tmp['remarks'][$key];
                $params[] = $tmp_params;
            }
        }
        if(isset($_REQUEST['resp']['keys'])){
            foreach($_REQUEST['resp']['keys'] as $key=>$filed){
                if(empty($filed)) continue;
                $tmp = $_REQUEST['resp'];
                $tmp_resp['key'] = $filed;
                $tmp_resp['id'] = $tmp['id'][$key]+0;
                $tmp_resp['pid'] = $tmp['pid'][$key]+0;
                $tmp_resp['level'] = $tmp['level'][$key]+0;
                $tmp_resp['must'] = $tmp['musts'][$key]+0;
                $tmp_resp['type'] = $tmp['types'][$key];
                $tmp_resp['rule'] = $tmp['rules'][$key];
                $tmp_resp['name'] = $tmp['names'][$key];
                $tmp_resp['remark'] = $tmp['remarks'][$key];
                $resp[] = $tmp_resp;
            }
        }
        if(isset($_REQUEST['err']['nos'])){
            foreach($_REQUEST['err']['nos'] as $key=>$filed){
                if(empty($filed) && $filed !== "0") continue;
                $tmp = $_REQUEST['err'];
                $tmp_err['no'] = $filed + 0;
                $tmp_err['msg'] = $tmp['msgs'][$key];
                $err[] = $tmp_err;
            }
        }
        $data['detail']['params'] = $params;
        $data['detail']['responses'] = $resp;
        $data['detail']['errinfo'] = $err;
        $data['create_time'] = time();

        $model = new Model();
        $response = $model->save($data);
//        $this->ajaxReturn(0,'',['id'=>$response]);
        if($response){
            $this->success();
        }else{
            $this->error();
        }
    }


    function parseRule(){
        if(isset($_REQUEST['rules']) && !empty($_REQUEST['rules'])){
            $ret = RulePaser::load($_REQUEST['rules']);
            $this->ajaxReturn(0,'',$ret);
        }else{
            $this->ajaxReturn(94,'empty');
        }
    }



    function group(){
        $fid = isset($_REQUEST['fid'])?$_REQUEST['fid']:0;
        $this->assign('fid',$fid);
        $this->display();
    }

    function groupList(){
        $model = new Model();
        $groups = $model->groups();
        $this->assign('groups',$groups);
        $this->display();
    }

    function listApi(){
        $model = new Model();
        $ret = $model->groupAllApi($_REQUEST['fid']);
        $ret['list'] = $this->handleApis($ret['list']);
        $this->assign('apis',$ret['list']);
        $this->display();
    }

    function saveGroup(){
        $data['title'] = $_REQUEST['title'];
        $data['desc'] = $_REQUEST['desc'];
        $data['fid'] = $_REQUEST['fid'];
        $data['id'] = $_REQUEST['id'];
        $data['version'] = isset($_REQUEST['version_key'])?$_REQUEST['version_key']:'';
        $configs = $environment = $params = [];
        if(isset($_REQUEST['params']['keys'])){
            foreach($_REQUEST['params']['keys'] as $key=>$filed){
                if(empty($filed)) continue;
                $tmp = $_REQUEST['params'];
                $tmp_params['key'] = $filed;
                $tmp_params['must'] = $tmp['musts'][$key]+0;
                $tmp_params['default'] = $tmp['defaults'][$key];
                $tmp_params['type'] = $tmp['types'][$key];
                $tmp_params['rule'] = $tmp['rules'][$key];
                $tmp_params['name'] = $tmp['names'][$key];
                $tmp_params['remark'] = $tmp['remarks'][$key];
                $params[] = $tmp_params;
            }
        }
        if(isset($_REQUEST['env']['names'])){
            foreach($_REQUEST['env']['names'] as $key=>$filed){
                if(empty($filed)) continue;
                $tmp = $_REQUEST['env'];
                $tmp_env['name'] = $filed;
                $tmp_env['url'] = $tmp['urls'][$key];
                $environment[] = $tmp_env;
            }
        }

        if(isset($_REQUEST['config']['names'])){
            foreach($_REQUEST['config']['names'] as $key=>$filed){
                if(empty($filed)) continue;
                $tmp = $_REQUEST['config'];
                $configs[$filed] = $tmp['keys'][$key];
            }
        }
        $data['detail']['config'] = $configs;
        $data['detail']['params'] = $params;
        $data['detail']['environment'] = $environment;
        $data['detail']['version'] = isset($_REQUEST['version'])?$_REQUEST['version']:[];
        $data['create_time'] = time();

        $model = new Model();
        $response = $model->save($data);
        if($response){
            $this->success();
        }else{
            $this->error();
        }
    }

    function run(){
        $id = $_REQUEST['id'] + 0;
        $model = new Model();
        $ret = $model->getApiById($id);
        $fid = explode(',',$ret['fid']);
        $fid = $fid[0];
        $ret['group'] = $model->getApiById($fid);

        $config = $ret['group']['detail']['config'];
        $environment = $ret['group']['detail']['environment'];
        $common_params = $ret['group']['detail']['params'];
        $class = "Tools\\Api\\" . $config['Class'];
        $params = $ret['detail']['params'];
        $params = array_merge($params,$common_params);

        $tmp_params = [];
        foreach($params as $par){
            $key = $par['key'];
            if(!isset($_REQUEST['params'][$key])) continue;
            $tmp_params[$key] = $_REQUEST['params'][$key];
        }
        $config['env'] = $_REQUEST['env'];
        $config['api'] = $ret['url'];

        $class = new $class;
        $ret = $class->api($config,$tmp_params);
        $ret = array_merge($ret,['config'=>$config,'params'=>$tmp_params]);
        $this->ajaxReturn(0,'',$ret);
    }

    function Index(){
        $fid = isset($_REQUEST['fid'])?$_REQUEST['fid']:0;
        $this->assign('fid',$fid);
        $this->display();
    }
    function showAll(){
        $model = new Model();
        $ret = $model->groupAllApi($_REQUEST['fid']);
        $ret['group']['detail'] = json_decode($ret['group']['detail'],true);
        $ret['list'] = $this->handleApis($ret['list']);
        $this->ajaxReturn(0,'',$ret);
    }


    function handleApis($list,$type=''){
        $group = $data = [];

        foreach($list as $api){

            if(!empty($api['url']) && $type=='group'){
                unset($data[$api['id']]);
                continue;
            }
            $count = count(explode(',',$api['fid']));
            $group[$count][] = $api;
            $id = $api['id'];
            $data[$id] = $api;
        }
        arsort($group);
        foreach($group as $apis){
            foreach($apis as $api){
                $fids = explode(',',$api['fid']);
                if(count($fids) == 1) break;
                $fid = end($fids);
                $data[$fid]['sub'][] = $api;
                unset($data[$api['id']]);
            }
        }

        return array_values($data);
    }

    function demo(){
        $this->success();
    }

    function delete(){
        $id = isset($_REQUEST['id'])?$_REQUEST['id']:0;
        $data['id'] = $id;
        $data['status'] = -1;
        $model = new Model();
        $ret = $model->update($data);
        if($ret){
            $this->success();
        }else{
            $this->error();
        }
    }

    function exportApi(){
        $id = isset($_REQUEST['id'])?$_REQUEST['id']:0;
        $this->assign('id',$id);
        $this->display();
    }

    function getApiById(){
        $id = $_REQUEST['id'] + 0;
        $model = new Model();
        $ret = $model->getApiById($id);

        $fid = explode(',',$ret['fid']);
        $fid = $fid[0];
        if($fid) $ret['group'] = $model->getApiById($fid);

        $type = isset($_REQUEST['type'])?$_REQUEST['type']:'';
        $acttype = isset($_REQUEST['acttype'])?$_REQUEST['acttype']:'';

        if($acttype == 'addapi'){
            $fid = $id;
        }

        if($type == 'group'){
            $list = $model->groupAllApi($fid);
            $ret['list'] = $this->handleApis($list['list'],'group');
        }

        $this->ajaxReturn(0,'',$ret);
    }

}
