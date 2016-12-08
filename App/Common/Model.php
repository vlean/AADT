<?php
/**
 * Created by PhpStorm.
 * User: tk
 * Date: 16-11-8
 * Time: 下午11:15
 */

namespace App\Common;


class Model
{
    function getApiById($id){
        $id = intval($id);
        $db = DB::getInstance();
        $ret = $db->query('select * from api_list where status=1 and id='.  $id);
        $ret = $this->format_api($ret);
        return $ret[0];
    }

    function saveApiInfo($data){
        if(isset($data['id'])){
            $sql = "update api set ";
            foreach($data as $key=>$val){
                $sql .="$key='$val'";
            }
            $sql = mb_substr($sql,0,mb_strlen($sql)-1);
            $sql .= " where id=$data[id]";
        }else{
            $keys = implode(",",array_keys($data));
            $vals = implode("','",array_values($data));
            $sql = "insert into api ($keys) VALUES($vals)";
        }
        return  DB::getInstance()->query($sql);
    }

    function groupAllApi($fid){
        $db =  DB::getInstance();
        $group = $db->query('select * from api_list where status=1  and id='.  $fid);
        $list = $db->query('select * from api_list where status=1 and find_in_set('.$fid .',`fid`)');
        return ['group'=>end($group),'list'=>$list];
    }
    function save($data){
        $data['detail'] = json_encode($data['detail']);
        $db = DB::getInstance();
        if(isset($data['id']) && !empty($data['id'])){
            $db->where('id',$data['id']);
            $db->update('api_list',$data);
            $id = $data['id'];
        }else{
            $id = $db->insert('api_list',$data);
        }

        if(empty($id)) echo $db->getLastError();
        return $id;
    }
    function groups(){
        $db = DB::getInstance();
        $ret = $db->query("select * from api_list where fid=0 and status=1");
        return $ret;
    }
    function apis($fid){
        $db = DB::getInstance();
        $ret = $db->query("select * from api_list where fid=$fid and status=1");
        return $ret;
    }

    function update($data){
        $db = DB::getInstance();
        $db->where('id',$data['id']);
        return $db->update('api_list',$data);
    }

    private function format_api($apis){
        foreach($apis as $key=>$api){
            $apis[$key]['detail'] = json_decode($api['detail'],true);
        }
        return $apis;
    }
}
