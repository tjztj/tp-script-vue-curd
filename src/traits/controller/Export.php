<?php

namespace tpScriptVueCurd\traits\controller;


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use think\facade\Cache;
use think\facade\Db;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\FieldDo;
use tpScriptVueCurd\tool\excel_out\ExportExcel;

/**
 * @property FieldCollection|ModelField[] $fields
 */
trait Export
{

    /**
     * #title 导出当前结果数据
     * @return void
     */
    public function export(){
        $indexGuid=$this->request->param('pageGuid');
        $indexGuid||$this->error('缺少参数');
        $sql=Cache::get('indexExportSql-'.$indexGuid);
        $sql||$this->error('请刷新页面后重试');

        $fields=$this->fields->filter(fn(ModelField $field)=>$field->canExport())->rendGroup();

        $ths=$this->getExportThead($fields);

        $list=Db::query($sql)?:[];
        FieldDo::doExportListBefore($this->fields,$list);

        $list=array_map(function ($v){
            $data=[];
            FieldDo::doExportBefore($this->fields,$v);
            foreach ($this->fields as $f){
                $data[$f->name()]=$f->getExportText($v);
            }
            FieldDo::doExportAfter($this->fields,$data,$v);
            return $data;
        },$list);



        $title=$this->getExportTitle();
        $excel=ExportExcel::make($title);
        $excel->defFormatText=true;
        $excel->showColBorder=true;
        $excel->showTableBorder=true;
        $excel->thBgColor='fffffbe6';
        $excel->title->alignmentHorizontal=Alignment::HORIZONTAL_LEFT;
        $excel->title->wrapText=true;
        $excel->title->height=40;
        $excel->fileName=$title.'_'.\tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d_H时i分s秒');
        $excel->setThead($ths);
        $excel->setData($list);
        $excel->out();
    }


    /**
     * 导出表名称
     * @return string
     */
    protected function getExportTitle():string{
        return $this->title;
    }

    /**
     * 获取导出数据的表头信息
     * @param FieldCollection $fields
     * @return array
     */
    private function getExportThead(FieldCollection $fields):array{
        $getThs=function ($fieldArr){
            /**
             * @var ModelField[] $fieldArr
             */
            $ths=[];
            foreach ($fieldArr as $v){
                $w=ceil($v->listColumnWidth()/10);
                $ths[]=['name'=>$v->name(),'value'=>$v->title(),'width'=>$w<10?20:$w];
            }
            return $ths;
        };
        $groupFields=$this->getExportGroupFields($fields);
        if(count($groupFields)===1){
            //如果只有一层分组，导出的数据不分组
            $ths=$getThs(current($groupFields));
        }else{
            $ths=[];
            foreach ($groupFields as $group=>$fieldArr){
                $ths[]=['value'=>$group,'childs'=>$getThs($fieldArr)];
            }
        }
        return $ths;
    }


    /**
     * 获取导出表分组的相关字段信息
     * @param FieldCollection $fields
     * @return array
     */
    private function getExportGroupFields(FieldCollection $fields):array{
        $fieldArr=[];
        foreach ($fields as $field){
            $group=$field->group();
            $group||$group='基本信息';
            isset($fieldArr[$group])||$fieldArr[$group]=[];
            $fieldArr[$group][]=$field;
        }
        return $fieldArr;
    }
}