<?php

namespace tpScriptVueCurd\traits\model;

use tpScriptVueCurd\option\generate_table\GenerateTableOption;

trait GenerateTable
{


    /**
     * 是否生成表
     * @param GenerateTableOption $table
     * @return bool
     */
    public function generateTable(GenerateTableOption $table):bool{
        return false;
    }

    /**
     * 执行表
     */
    private function doGenerateSql():void{
        if(!app()->isDebug()){
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
        dd($sql);
    }

}