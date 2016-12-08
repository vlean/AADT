<?php
/**
 * Created by PhpStorm.
 * User: tk
 * Date: 16-10-23
 * Time: 下午7:29
 */

namespace Tools;


class MultiProcess
{
    public $maxProcessNum = 10;
    public $subProcessNum = 0;

    public function assignTask($jobs){
        Log::record('start sub task:'.posix_getpid(),Log::DEBUG);
        sleep(10);
        Log::record('finish sub task:'.posix_getpid(),Log::DEBUG);
        exit();
    }

    public function processControll($jobs){
        Log::record('start assign sub task',Log::DEBUG);
        $pid = pcntl_fork();
        if($pid == 0){
            $this->assignTask($jobs);
        }elseif ($pid == -1){

        }else{
            Log::record('assign sub task succ', Log::DEBUG);
            $this->subProcessNum++;
        }
    }

    public function init(){
        while(True){
            if(($jobs = $this->hasJobs()) == false && $this->subProcessNum == 0){
                sleep(1);
                Log::record('sleep 1, no jobs');
                continue;
            }

            if((empty($jobs) && $this->subProcessNum < $this->maxProcessNum)  || $this->subProcessNum >= $this->maxProcessNum){
                Log::record('no jobs, waiting sub process end');
                pcntl_wait($status);
                $this->subProcessNum--;
            }

            if(!empty($jobs))$this->processControll($jobs);
        }
    }

    public function hasJobs(){
        return false;
    }
}