<?php
/**
 * Task
 * 校验接口结构
 * 校验接口值
 */

namespace Tools;


class ApiTest
{
    private $testResult = "";



    public function compare($source, $target,$prex=''){
        $target = is_array($target)? $target:json_decode($target,true);
        $source = is_array($source)? $source:json_decode($source,true);
        /**
         * key int() 1-5/1,2,3;not null;
         *     flo(2,3)
         *     str() 1-5/
         *     arr
         *     mod
         *     nul
         *
         * type
         * length
         * subLength 0
         * size[start,end]
         * canNull
         * extRules
         */

        foreach ($source as $key=>$rule){

            if(!$this->beforeCmp($rule,$target,$key)) continue;
            $val = $target[$key];
            switch($rule['type']){
                case 'int':
                    $this->cmpInteger($rule,$key,$val);
                    break;
                case 'flo':
                    $this->cmpFloat($rule,$key,$val);
                    break;
                case 'str':
                    $this->cmpString($rule,$key,$val);
                    break;
                case 'arr':
                    $this->cmpArray($rule,$key,$val);
                    foreach($val as $arr){
                        $this->compare($rule['extRules'],$arr,$key);
                    }
                    break;
                case 'mod':
                    $this->cmpModel($rule,$key,$val);
                    $this->compare($rule['extRules'],$val,$key);//回调比较
                    break;
            }
        }
    }
    
    private function beforeCmp($rule,$target,$key){
        if(!isset($target[$key]) && (isset($rule['isMust']) && $rule['isMust'])){
            $this->addResult($key,'不能缺失',E_USER_ERROR);
            return false;
        }
        $val = $target[$key];
        if(is_null($val) && !(isset($rule['canNull']) && $rule['canNull']) ){
            $this->addResult($key,'值不能为null');
            return false;
        }else{
            return true;
        }
    }


    private function cmpInteger($rule,$key,$val){
        if(!is_integer($val))$this->addResult($key,'值类型不为int:'.$val);

        if(isset($rule['length']) && strlen($val) > $rule['length']){
            $this->addResult($key,'值超过范围:'.$val .' length:'.$rule['length']);
        }


        if(isset($rule['min']) && $val<$rule['min']){
            $this->addResult($key,'值小于最小值:'.$val.' min:'.$rule['min']);
        }

        if(isset($rule['max']) && $val>$rule['max']){
            $this->addResult($key,'值大于最大值:'.$val.' max:'.$rule['max']);
        }
        if(isset($rule['allow']) && !in_array($val,$rule['allow'])){
            $this->addResult($key,'值不被允许:'.$val. ' allow:'.json_encode($rule['allow']));
        }
    }

    private function cmpFloat($rule,$key,$val){
        if(!is_float($val))$this->addResult($key,'值类型不为float:'.$val);

        if(isset($rule['length']) && strlen($val) > $rule['length']){
            $this->addResult($key,'值超过范围:'.$val .' length:'.$rule['length']);
        }


        if(isset($rule['min']) && $val<$rule['min']){
            $this->addResult($key,'值小于最小值:'.$val.' min:'.$rule['min']);
        }

        if(isset($rule['max']) && $val>$rule['max']){
            $this->addResult($key,'值大于最大值:'.$val.' max:'.$rule['max']);
        }
        if(isset($rule['allow']) && !in_array($val,$rule['allow'])){
            $this->addResult($key,'值不被允许:'.$val. ' allow:'.json_encode($rule['allow']));
        }
    }

    private function cmpString($rule,$key,$val){
        if(!is_string($val))$this->addResult($key,'值类型不为string:'.$val);

        if(isset($rule['length']) && strlen($val) > $rule['length']){
            $this->addResult($key,'值超过范围:'.$val .' length:'.$rule['length']);
        }

        if($val === ''){
            $this->addResult($key,'值为空字符串',E_USER_NOTICE);
        }
    }

    private function cmpArray($rule,$key,$val){

    }

    private function cmpModel($rule,$key,$val){

    }

    private function cmpNull($rule,$key,$val){

    }

    public function addResult($key,$info,$level=E_USER_WARNING){
        $this->testResult[$level][] = "[$key] $info";

    }
    public function getResult(){
        return $this->testResult;
    }

    private function isInt($source,$rule){

    }

}