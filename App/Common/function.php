<?php
/**
 * Created by PhpStorm.
 * User: vlean
 * Date: 2016/12/6
 * Time: 下午1:16
 */



function showList($list){
    foreach($list as $api){
        $fid = explode(',',$api['fid']);
        $fid = $fid[0];
        $html = "<li><a href='/api/index?id=$api[id]&fid=$fid&group=$api[fid]&type=run'>$api[title]</a>";
        if(isset($api['url']) && empty($api['url'])){

            $html .= "<small><a href='/api/delete?id=$api[id]'><span class='glyphicon glyphicon-minus'></span></a></small>";
            $html .= "<small><a href='/api/edit?fid=$api[fid],$api[id]&type=addapi'><span class='glyphicon glyphicon-plus'></span></a></small>";
            $html .= "<small><a href='/api/group?fid=$api[fid],$api[id]&type=editgroup'><span class='glyphicon glyphicon-edit'></span></a></small>";
        }else{
            $html .=" <small><a href='/api/delete?id=$api[id]'>del</a></small>"
                ."<small><a href='/api/edit?id=$api[id]&fid=$api[fid]&type=cloneapi'>copy</a></small>"
                ."<small><a href='/api/edit?id=$api[id]&fid=$api[fid]&type=editapi'>edit</a></small>"
                ."<small><a href='/api/exportApi?id=$api[id]&type=export'>html</a></small>"
                ."</li>";
        }
        echo $html;
        if(isset($api['sub']) && count($api['sub'])){
            echo "<ul>";
            showList($api['sub']);
            echo "</ul>";
        }
    }
}