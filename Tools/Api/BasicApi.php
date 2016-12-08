<?php

namespace Tools\Api;

abstract class BasicApi
{
    protected $header = array();
    protected $timeout = 5;

    /**
     * 基础curl请求方法
     * @param $url 接口地址
     * @param $data 接口参数
     * @param array $header
     * @return string
     */
    public function http($url, $data, $header=array()){
        $header = empty($header)?$this->header:$header;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($ch);
        $curl_info = explode("\r\n\r\n",$output,2);
        if(count($curl_info)==2){
            $header = $curl_info[0];
            $body = $curl_info[1];
        }else{
            $header = [];
            $body = $curl_info;
        }
        $body = empty($body) && empty($header)?$output:$body;
        $info = curl_getinfo($ch);
        curl_close($ch);
        return ['header'=>$header,'body'=>$body,'info'=>$info];
    }

    /**
     * 加密数据
     * @param $config
     * @param $data
     * @return mixed
     */
    abstract function encrypt($config, $data);

    /**
     * 解析
     * @param $config
     * @param $data
     * @return mixed
     */
    abstract function decrypt($config, $data);

    /**
     * 解析请求url
     * @param $config
     * @return mixed
     */
    abstract function parseUrl($config);

    /**
     * 解析返回内容
     * @param $output
     * @return mixed
     */
    abstract function parse($output);

    /**
     * 初始化配置
     * @param $config
     * @param $data
     * @return mixed
     */
    abstract function init($config,$data);

    /**
     * 设置http header
     * @param array $header
     */
    public function setApiHeader(array $header){
        $this->header = $header;
    }


    /**
     * 请求api
     * @param $config
     * @param $data
     * @return mixed
     */
    public function api($config, $data){
        $this->init($config,$data);
        $data = $this->encrypt($config, $data);
        $url  = $this->parseUrl($config);
        $result = $this->http($url, $data, $this->header);
        $output = $this->parse($result['body']);

        $ret['info']['times'] = $result['info']['total_time'];
        $ret['info']['code'] = $result['info']['http_code'];
        $ret['info']['header'] =$result['header'];
        $ret['info']['body'] =$result['body'];
        $ret['info']['url'] = $url;
        $ret['info']['params'] = $data;
        $ret['info']['urlDetail'] = $url . '?'. $data;

        if(!$output){
            $ret['response']['errno'] = -9999;
            $ret['response']['msg']  = 'API未知错误';
            $ret['response']['data'] =  '';
        }else{
            $ret['response']= $output['response'];
        }
        return $ret;
    }
}
