<?php


namespace tpScriptVueCurd\traits\controller;



use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\field\RegionField;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use think\Request;
use tpScriptVueCurd\option\FunControllerImportAfter;
use tpScriptVueCurd\option\FunControllerImportBefore;

/**
 * Trait Excel
 * @property Request $request
 * @property BaseModel $md
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait Excel
{
    public FieldCollection $fields;

    private bool $baseAndChildImport=true;//是父表+子表 列表导入
    /**
     * @var BaseModel[]
     */
    private array $importBaseInfos;//当前父表导入的数据集合



    protected function myExcelFields():FieldCollection{
        if(is_null($this->getParentController())){
            return $this->fields;
        }
        //不需要村社，父表已经有村社了
        return $this->fields->filter(fn(ModelField $v)=>!$v instanceof RegionField);
    }


    /**
     * 导入关键参数(字段集合)，可继承然后重写
     * @return FieldCollection
     */
    protected function excelFields():FieldCollection{
        if(!$this->baseAndChildImport||empty($this->getChildControllers())){//只导入本表
            return $this->myExcelFields();
        }
        //获取父表字段
        $fields=$this->myExcelFields()->map(function(ModelField $field){
            $field=clone $field;
            $field->name('PARENT|'.$field->name());
            $field->title($this->title.'|'.$field->title());
            return $field;
        });


        //子表字段
        foreach ($this->getChildControllers() as $v){
            /**
             * @var Controller $v
             * @var BaseModel $model
             */
            $model=$v->md;

            $modelName=class_basename($model);
            $fields=$fields->merge(
                $model->fields()
                    ->filter(fn(ModelField $v)=>!$v instanceof RegionField)
                    ->map(function(ModelField $field)use($modelName,$v){
                        $field=clone $field;
                        $field->name($modelName.'|'.$field->name());
                        $field->title($v->title.'|'.$field->title());
                        return $field;
                    })
            );
        }


        return $fields;
    }

    /**
     * 导入关键参数(excel标题)，可继承然后重写
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function excelTilte():string{
        return $this->title;
    }


    protected function myExcelSave(array $saveData):BaseModel{
        static $modelClassName;
        if(!isset($modelClassName)){
            $modelClassName=get_class($this->md);
        }

        $this->excelBaseInfo=null;
        if(!is_null($this->getParentController())){
            $baseId=$this->request->param('base_id/d');
            if(empty($baseId)){
                throw new \think\Exception('缺少父表参数');
            }
            $this->excelBaseInfo=(clone $this->getParentController()->md)->find($baseId);
            if(empty($this->excelBaseInfo)){
                throw new \think\Exception('未找到父表相关信息');
            }
        }

        $option=new FunControllerImportBefore();
        $option->saveArr=$saveData;
        $option->base=$this->excelBaseInfo;
        $this->importBefore($option);

        /**
         * @var BaseModel $model
         */
        $model=new $modelClassName;
        $info=$model->addInfo($option->saveArr, $option->base,$this->myExcelFields(),true);

        $optionAfter=new FunControllerImportAfter();
        $optionAfter->saveObjects=$info;
        $optionAfter->base=$this->excelBaseInfo;
        $this->importAfter($optionAfter);

        return $info;
    }

    /**
     * 执行添加逻辑，可继承然后重写
     * @param array $saveData
     * @return BaseModel|array
     * @throws \think\Exception
     */
    protected function excelSave(array $saveData){
        if(!$this->baseAndChildImport||empty($this->getChildControllers())){//只导入本表
            return $this->myExcelSave($saveData);
        }

        $datas=[];
        foreach ($saveData as $k=>$v){
            $arr=explode('|',$k);
            isset($datas[$arr[0]])||$datas[$arr[0]]=[];
            $datas[$arr[0]][$arr[1]]=$v;
        }
        $baseId=$this->getMainIdByImportData($datas['PARENT']);
        $infos=[
            get_class($this->md)=>$this->importBaseInfos[$baseId],
        ];


        //子表
        /**
         * @var Controller $childController
         * @var BaseModel $model
         * @var Controller[] $childControllerClassList
         */
        $childControllerClassList=[];
        foreach ($this->getChildControllers() as $childController){
            $modelClass=get_class($childController->md);
            $modelName=class_basename($modelClass);
            if(isset($datas[$modelName])){
                $model=new $modelClass;
                $base=$this->getExcelBaseInfo($baseId);


                $option=new FunControllerImportBefore();
                $option->saveArr=$datas[$modelName];
                $option->base=$base;
                $childController->importBefore($option);
                $infos[$modelClass]=$model->addInfo($option->saveArr,$option->base,$model->fields(),true);

                $optionAfter=new FunControllerImportAfter();
                $optionAfter->saveObjects=$infos[$modelClass];
                $optionAfter->base=$option->base;

                $childController->importAfter($optionAfter);
            }
        }
        return $infos;
    }



    ####################################################################################################################
    /**
     * 根据导入数据获取 导入的ID
     * @param array $mainData
     * @return mixed
     */
    private function getMainIdByImportData(array $mainData):int{
        static $baseIds=[];

        //父表字段的值一样将会视作同一条父数据
        $baseIdsKey=serialize($this->myExcelFields()->setSave($mainData,clone $this->md,true)->getSave());
        if(!isset($baseIds[$baseIdsKey])){
            $parentInfo=$this->myExcelSave($mainData);
            $baseIds[$baseIdsKey]=$parentInfo->id;
            $this->setExcelBaseInfo($parentInfo);
        }
        return $baseIds[$baseIdsKey];
    }
    protected function getExcelBaseInfos():array{return $this->importBaseInfos?:[];}
    protected function getExcelBaseInfo(int $baseId):BaseModel{
        if(empty($baseId)){
            throw new \think\Exception('父表ID错误');
        }

        if(!isset($this->importBaseInfos[$baseId])){
            $this->importBaseInfos[$baseId]=$this->md->find($baseId);
        }
        if(empty($this->importBaseInfos[$baseId])){
            throw new \think\Exception('未找到相关周信息');
        }
        return $this->importBaseInfos[$baseId];
    }
    private function setExcelBaseInfo(BaseModel $info):void{
        $this->importBaseInfos[$info->id]=$info;
    }


    ####################################################################################################################





    /**
     * #title 导入模板数据
     * @return \think\response\Json|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function importExcelTpl(){
        set_time_limit(0);
        ini_set('memory_limit', -1);
        $file = $this->request->file('file');
        if (empty($file)) {
            return $this->errorAndCode('未获取到上传文件');
        }
        $base_path= public_path( '/upload');
        is_dir($base_path) || mkdir($base_path);
        $base_path= public_path( '/upload/excel_import');
        is_dir($base_path) || mkdir($base_path);
        $options = ['img_save_path' => $base_path.'/'. \tpScriptVueCurd\tool\Time::unixtimeToDate('Ymd') . '/', 'format' => []];
        is_dir($options['img_save_path']) || mkdir($options['img_save_path']);
        try{
            $data = \tpScriptVueCurd\tool\Excel::importExecl($file->getRealPath(), 0, 0, $options);
        }catch (\Exception $e){
            return $this->error($e);
        }



        ['expCellName'=>$expCellName,'row'=>$row]=$this->parseExpFields();
        //判断标题是否一致，有些表字段一样的，防止导错
        if(preg_replace('/\s+/','',current($data[1]))!==preg_replace('/\s+/','',$this->getExcelTilte())){
            return $this->errorAndCode('模版错误，请重新下载最新的模版-001');
        }


        $names=[];
        foreach ($expCellName as $k => $v) {
            //对比模版，老模版提示错误
            if (preg_replace('/\s+/','',current($data[2])) !== preg_replace('/\s+/','',$v[1])||preg_replace('/\s+/','',current($data[3])) !== preg_replace('/\s+/','',$row[$v[0]])) {
                return $this->errorAndCode('模版错误，请重新下载最新的模版-002');
            }
            $names[key($data[2])]=$v[0];
            next($data[2]);
            next($data[3]);
        }
        //去掉模版上的提示行
        unset($data[1],$data[2], $data[3]);
        if(empty($data)){
            return $this->errorAndCode('未找到可导入的数据');
        }


        $this->md->startTrans();
        $last_do_row=4;
        try{
            //因为$data排序已经乱了，所以我用while遍历
            while (isset($data[$last_do_row])){
                $saveData=[];
                $isEmptyRow=true;
                foreach ($data[$last_do_row] as $key=>$val){
                    if(!isset($names[$key])){
                        continue;
                    }
                    $saveData[$names[$key]]=$val;
                    if(is_string($val)&&trim($val)!==''){
                        $isEmptyRow=false;
                    }
                }
                if($isEmptyRow){
                    //各行之间不能又空行，防止有些excel空行过多，资源不足
                    break;
                }
                $this->excelSave($saveData);//执行之类或自己的方法
                $last_do_row++;
            }
        }catch (\Exception $e){
            $this->md->rollback();
            $this->errorAndCode('Excel第'.$last_do_row.'行 '.$e->getMessage(),$e->getCode());
        }

        $this->md->commit();
        $this->success('导入成功');
    }



    /**
     * #title 下载excel模板
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function downExcelTpl():void{

        ['expCellName'=>$expCellName,'row'=>$row]=$this->parseExpFields();
        $title=$this->getExcelTilte();
        $data = [
            'title' => $title,
            'expTitle' => $this->getExcelFieldName(),
            'title_horizontal' => Alignment::HORIZONTAL_LEFT,
            'freezePane' => 4,
            'expCellName' => $expCellName,
            'expTableData' => [$row],
        ];
        \tpScriptVueCurd\tool\Excel::exportExecl($data);
    }

    /**
     * #title 仅本表导入模板下载
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function justDownBaseExcelTpl():void{
        $this->baseAndChildImport=false;
        $this->downExcelTpl();
    }

    /**
     * #title 仅本表数据导入
     * @return \think\response\Json|void
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function justImportBaseExcelTpl(){
        $this->baseAndChildImport=false;
        return $this->importExcelTpl();
    }


    /**
     * 获取excel头部字段配置
     * @return ExcelFieldTpl[]
     */
    private function getTHeadExpFields():array{
        $expFields = [];
        $this->getExcelFields()->each(function(ModelField $v)use(&$expFields){
            if(!$v->canExcelImport()){
                return;
            }

            if(!$v->editShow()){
                if(!$v instanceof RegionField){
                    return;
                }
                $hasShow=false;
                foreach ($v->getAboutRegions() as $val){
                    if($val->editShow()){
                        $hasShow=true;
                        break;
                    }
                }
                if(!$hasShow){
                    return;
                }
            }

            $excelFieldTpl=new ExcelFieldTpl($v->name(),$v->title());
            $v->excelTplExplain($excelFieldTpl);
            $ext=$v->ext();
            if($ext){
                $excelFieldTpl->wrapText=true;
                if($excelFieldTpl->explain){
                    $excelFieldTpl->explain.="\n";
                }
                $excelFieldTpl->explain.="（{$ext}）";
            }
            $expFields[]=$excelFieldTpl;
        });
        return $expFields;
    }


    /**
     * 解析 getTHeadExpFields 得到的数据
     */
    private function parseExpFields():array{
        $expFields=$this->getTHeadExpFields();
        $expCellName=[];
        $row=[];
        foreach ($expFields as $v){
            $th=[$v->name,$v->title];
            if($v->width){
                $th['width']=$v->width;
            }
            if($v->wrapText){
                $th['wrap_text']=$v->wrapText;
            }
            if($v->isText){
                $th['is_text']=$v->isText;
            }
            $row[$v->name]=$v->explain;
            $expCellName[]=$th;
        }

        return ['expCellName'=>$expCellName,'row'=>$row];
    }


    /**
     * 防止重复执行
     * @return FieldCollection
     */
    private function getExcelFields():FieldCollection{
        static $return;
        if(!isset($return)){
            $return=$this->excelFields();
        }
        return $return;
    }

    /**
     * 防止重复执行
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getExcelTilte():string{
        static $return;
        if(!isset($return)){
            $return=" ".$this->excelTilte()."\n（合并行的单元格，将看成是同样的值；列合并暂不支持）";
        }
        return $return;
    }


    /**
     * 防止重复执行
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    private function getExcelFieldName():string{
        static $return;
        if(!isset($return)){
            $return='导入模版__' .$this->excelTilte() . \tpScriptVueCurd\tool\Time::unixtimeToDate('Y-m-d_H时i分s秒');
        }
        return $return;
    }

}