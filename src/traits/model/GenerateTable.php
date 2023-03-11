<?php

namespace tpScriptVueCurd\traits\model;

use think\facade\Db;
use tpScriptVueCurd\option\generate_table\GenerateTableOption;

trait GenerateTable
{


    /**
     * 是否生成表
     * @param GenerateTableOption $table 对生成表的配置
     * @return bool
     */
    public function generateTable(GenerateTableOption $table):bool{
        return false;
    }

    /**
     * 执行表的 自动生成
     */
    private function doGenerateTable():void{
        if(!appIsDebug()){
            return;
        }

        //只执行一次
        static $init=false;
        if($init===true){
            return;
        }
        $init=true;


        $table=new GenerateTableOption($this);
        $isGenerateTable=$this->generateTable($table);
        if($isGenerateTable===false){
            return;
        }
        $sql=$table->getSql();
        if($sql){
            Db::execute($sql);
        }

    }

}