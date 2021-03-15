<?php


namespace tpScriptVueCurd\traits\controller;




use tpScriptVueCurd\base\controller\BaseChildController;
use tpScriptVueCurd\base\model\BaseChildModel;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use think\Request;
use tpScriptVueCurd\option\FunControllerChildImportAfter;
use tpScriptVueCurd\option\FunControllerChildImportBefore;

/**
 * Trait ExcelHaveChild
 * @property Request $request
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait ExcelHaveChild
{
    use Excel{
        Excel::downExcelTpl as parentDownExcelTpl;
        Excel::importExcelTpl as parentImportExcelTpl;
        Excel::excelFields as parentExcelFields;
        Excel::excelSave as parentExcelExcelSave;
    }
    public VueCurlModel $model;
    public FieldCollection $fields;

    private bool $baseAndChildImport=true;//是父表+子表 列表导入
    /**
     * @var BaseModel[]
     */
    private array $importBaseInfos;//当前父表导入的数据集合




    /**
     * #title 父表+子表导入模板下载
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function downExcelTpl():void{
        $this->baseAndChildImport=true;
        $this->parentDownExcelTpl();
    }


    /**
     * #title 子表导入模板下载
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function justDownBaseExcelTpl():void{
        $this->baseAndChildImport=false;
        $this->parentDownExcelTpl();
    }


    /**
     * #title 父表+子表的数据导入
     * @return \think\response\Json|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function importExcelTpl(){
        $this->baseAndChildImport=true;
        return $this->parentImportExcelTpl();
    }


    /**
     * #title 子表数据导入
     * @return \think\response\Json|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    function justImportBaseExcelTpl(){
        $this->baseAndChildImport=false;
        return $this->parentImportExcelTpl();
    }


    /**
     * 导入关键参数(字段集合)
     * @return FieldCollection
     */
    protected function excelFields():FieldCollection{
        if(!$this->baseAndChildImport){//只导入父表
            return $this->parentExcelFields();
        }

        //获取父表字段
        $fields=$this->parentExcelFields()->map(function(ModelField $field){
            $field=clone $field;
            $field->name('PARENT|'.$field->name());
            $field->title(static::getTitle().'|'.$field->title());
            return $field;
        });

        //子表字段
        foreach (static::childModelObjs() as $childControllerClass=>$model){
            /* @var BaseChildModel $model
             * @var BaseChildController|string $childControllerClass
             */
            $modelName=class_basename($model);
            $fields=$fields->merge(
                $model->fields()
                    ->filter(fn(ModelField $v)=>!in_array($v->name(),[$this->model::getRegionField(),$this->model::getRegionPidField()]))
                    ->map(function(ModelField $field)use($modelName,$childControllerClass){
                        $field=clone $field;
                        $field->name($modelName.'|'.$field->name());
                        $field->title($childControllerClass::getTitle().'|'.$field->title());
                        return $field;
                    })
            );
        }
        return $fields;
    }


    /**
     * 导入关键参数(excel标题)
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function excelTilte():string{
        return static::getTitle();
    }


    /**
     * 执行添加逻辑
     * @param $saveData
     * @return mixed
     */
    protected function excelSave($saveData){
        if(!$this->baseAndChildImport){//仅导入父表数据
            return [
                static::modelClassPath()=>$this->parentExcelExcelSave($saveData)
            ];
        }

        $datas=[];
        foreach ($saveData as $k=>$v){
            $arr=explode('|',$k);
            isset($datas[$arr[0]])||$datas[$arr[0]]=[];
            $datas[$arr[0]][$arr[1]]=$v;
        }
        $baseId=$this->getMainIdByImportData($datas['PARENT']);
        $infos=[
            static::modelClassPath()=>$this->importBaseInfos[$baseId],
        ];


        //子表
        /**
         * @var BaseChildController|string $childControllerClass
         * @var BaseChildModel $model
         * @var BaseChildController[] $childControllerClassList
         */
        $childControllerClassList=[];
        foreach (static::childControllerClassPathList() as $childControllerClass){
            $modelClass=$childControllerClass::modelClassPath();
            $modelName=class_basename($modelClass);
            if(isset($datas[$modelName])){
                $model=new $modelClass;
                $base=$this->getExcelBaseInfo($baseId);

                isset($childControllerClassList[$childControllerClass])||$childControllerClassList[$childControllerClass]=new $childControllerClass(app());

                $option=new FunControllerChildImportBefore();
                $option->saveArr=$datas[$modelName];
                $option->base=$base;
                $childControllerClassList[$childControllerClass]->importBefore($option);
                $infos[$modelClass]=$model->addInfo($option->saveArr,$option->base,true);

                $optionAfter=new FunControllerChildImportAfter();
                $optionAfter->saveObjects=$infos[$modelClass];
                $optionAfter->base=$option->base;

                $childControllerClassList[$childControllerClass]->importAfter($optionAfter);
            }
        }
        return $infos;

    }


    /**
     * 根据导入数据获取 导入的ID
     * @param array $mainData
     * @return mixed
     */
    private function getMainIdByImportData(array $mainData):int{
        static $baseIds=[];

        //父表字段的值一样将会视作同一条父数据
        $baseIdsKey=serialize($this->parentExcelFields()->setSave($mainData,true)->getSave());
        if(!isset($baseIds[$baseIdsKey])){
            $baseInfo=$this->parentExcelExcelSave($mainData);
            $baseIds[$baseIdsKey]=$baseInfo->id;
            $this->setExcelBaseInfo($baseInfo);
        }
        return $baseIds[$baseIdsKey];
    }



    protected function getExcelBaseInfos():array{return $this->importBaseInfos?:[];}
    protected function getExcelBaseInfo(int $baseId):BaseModel{
        if(empty($baseId)){
            throw new \think\Exception('父表ID错误');
        }

        if(!isset($this->importBaseInfos[$baseId])){
            $this->importBaseInfos[$baseId]=$this->model->find($baseId);
        }
        if(empty($this->importBaseInfos[$baseId])){
            throw new \think\Exception('未找到相关周信息');
        }
        return $this->importBaseInfos[$baseId];
    }

    private function setExcelBaseInfo(BaseModel $info):void{
        $this->importBaseInfos[$info->id]=$info;
    }
}