<?php


namespace tpScriptVueCurd\traits\controller;



use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use think\db\exception\DataNotFoundException;
use think\db\exception\DbException;
use tpScriptVueCurd\tool\excel_out\ExportCell;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use think\Request;
use tpScriptVueCurd\option\FunControllerImportAfter;
use tpScriptVueCurd\option\FunControllerImportBefore;
use tpScriptVueCurd\tool\excel_out\ExportExcel;
use tpScriptVueCurd\tool\excel_out\ExportOperation;
use tpScriptVueCurd\tool\excel_out\ExportThCell;

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

    /**
     * @var bool 如果是，模板中将填写子表的数据；如果否模板中指挥导入本表的数据
     */
    protected bool $baseAndChildImport=false;//是否父表+子表 列表导入
    /**
     * @var BaseModel[]
     */
    protected array $importBaseInfos;//当前父表导入的数据集合



    public function myExcelFields():FieldCollection{
        if(is_null($this->getParentController())){
            return $this->fields;
        }
        //不需要村社，父表已经有村社了
        return $this->fields;
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
            if(!$v->parentImportSelf){
                continue;
            }
            $model=$v->md;

            $modelName=class_basename($model);
            $fields=$fields->merge(
                $model->fields()
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
            //可以在外面赋值
            $baseId=$this->request->get('base_id/d',0);
            $baseId||$baseId=$this->request->param('base_id/d',0);
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
        $option->all=$this->allData;
        $this->importBefore($option);

        /**
         * @var BaseModel $model
         */
        $model=new $modelClassName;
        $model->controller=$this;
        $info=$model->addInfo($option->saveArr, $option->base,$this->myExcelFields()->excelFilter(),true);

        $optionAfter=new FunControllerImportAfter();
        $optionAfter->saveObjects=$info;
        $optionAfter->base=$this->excelBaseInfo;
        $optionAfter->all=$this->allData;
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
            if(!$childController->parentImportSelf){
                continue;
            }
            $modelClass=get_class($childController->md);
            $modelName=class_basename($modelClass);
            if(isset($datas[$modelName])){
                $model=new $modelClass;
                $model->controller=$childController;
                $base=$this->getExcelBaseInfo($baseId);


                $option=new FunControllerImportBefore();
                $option->saveArr=$datas[$modelName];
                $option->base=$base;
                $option->all=$this->allData;
                $childController->importBefore($option);
                $infos[$modelClass]=$model->addInfo($option->saveArr,$option->base,$childController->myExcelFields()->excelFilter(),true);

                $optionAfter=new FunControllerImportAfter();
                $optionAfter->saveObjects=$infos[$modelClass];
                $optionAfter->base=$option->base;
                $optionAfter->all=$this->allData;

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
     * @throws \think\Exception
     */
    private function getMainIdByImportData(array $mainData):int{
        static $baseIds=[];

        //父表字段的值一样将会视作同一条父数据
        $baseIdsKey=serialize($this->myExcelFields()
            ->excelFilter()
            ->setSave($mainData,clone $this->md,true)->getSave());
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
//        $base_path= public_path( '/upload');
//        is_dir($base_path) || mkdir($base_path);
//        $base_path= public_path( '/upload/excel_import');
//        is_dir($base_path) || mkdir($base_path);
        $options = ['img_save_path' => public_path( '/upload/excel_import').'/'. \tpScriptVueCurd\tool\Time::unixtimeToDate('Ymd') . '/', 'format' => []];
//        is_dir($options['img_save_path']) || mkdir($options['img_save_path']);
        try{
            $data = \tpScriptVueCurd\tool\excel_in\Excel::importExecl($file->getRealPath(), 0, 0, $options);
        }catch (\Exception $e){
             $this->error($e);
        }


        $names=$this->checkExcelTpl($data);
        if(empty($data)){
             $this->errorAndCode('未找到可导入的数据');
        }


        $this->md->startTrans();
        $saveI=4;
        try{
            $last_do_row=$saveI;
            $saveArr=[];
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
                $saveArr[$last_do_row]=$saveData;
                $last_do_row++;
            }
            $this->allData=$saveArr;
            foreach ($saveArr as $i=>$v){
                $saveI=$i;
                $this->excelSave($v);//执行之类或自己的方法
            }
        }catch (\Exception $e){
            $this->md->rollback();
            $this->errorAndCode('Excel第'.$saveI.'行 '.$e->getMessage(),$e->getCode());
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
        $title=$this->getExcelTilte();
        $excel=ExportExcel::make($title);

        ['cell'=>$expCellName,'row'=>$row]=$this->parseExpFields();
        $emptyItem=[];

        $do=function (&$v)use(&$emptyItem,$row){
            $cell=['name'=>$v['name'],'value'=>$row[$v['name']]?:'','wrapText'=>true,'fontBold'=>false];
            if(isset($v['format'])){
                $cell['format']=$v['format'];
            }
            $v['childs']=[$cell];
            //空数据
            $emptyItem[$v['name']]='';
        };
        $haveGroup=false;
        foreach ($expCellName as $k=>$v){
            if(empty($v['childs'])){
                $do($expCellName[$k]);
            }else{
                $haveGroup=true;
                foreach ($v['childs'] as $key=>$val){
                    $do($expCellName[$k]['childs'][$key]);
                }
            }
        }
        $list=[];
        //生成2000条空数据
        ini_set('memory_limit','-1');
        for($i=0;$i<100;$i++){
            $list[]=$emptyItem;
        }


        foreach ($row as $v){
            $findCount=substr_count($v,"\n");
            if($findCount>=5||strlen($v)>=120){
                $excel->thRowMaxHeight=104;
                break;
            }
        }

        $excel->defFormatText=true;
        $excel->title->alignmentHorizontal=Alignment::HORIZONTAL_LEFT;
        $excel->title->wrapText=true;
        $excel->title->height=30;
        $excel->setThead($expCellName);
        $excel->fileName=$this->getExcelFieldName();
        $excel->setData($list);
        $excel->out();
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
                return;
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
            $excelFieldTpl->items=$v->excelSelectItems();
            $excelFieldTpl->type=$v->getType();
            $excelFieldTpl->field=$v;
            $excelFieldTpl->group=$v->group();
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
        $groupKeys=[];
        foreach ($expFields as $v){
            $th=['name'=>$v->name,'value'=>$v->title,'fontSize'=>13,'fontColor'=>'FF000000','alignmentVertical'=>Alignment::VERTICAL_CENTER,'fontBold'=>true,'fontName'=>'Microsoft YaHei UI Light'];
            if($v->width){
                $th['width']=$v->width;
            }
            if($v->wrapText){
                $th['wrapText']=$v->wrapText;
            }
            if($v->isText){
                $th['formatText']=$v->isText;
            }
            if($v->field->required()){
                $objRichText = new RichText();
                $objRichText->createTextRun('*')->getFont()
                    ->setColor(new Color('FFCF1322'))
                    ->setBold($th['fontBold'])
                    ->setSize($th['fontSize'])
                    ->setName('Calibri');
                $objRichText->createTextRun($th['value'])->getFont()
                    ->setColor(new Color($th['fontColor']))
                    ->setBold($th['fontBold'])
                    ->setSize($th['fontSize'])
                    ->setName($th['fontName']);
                $th['value']=$objRichText;
            }
            if($v->items){
                $th['format']= static function ($row, ExportCell $cell)use($v){
                    if($cell instanceof ExportThCell){
                        return '';
                    }
                    $cell->do= static function (Spreadsheet $excel, ExportCell $cell)use($v){
                        //如果是表头
                        $checkSheet=$excel->getSheetByName('选项');
                        if(empty($checkSheet)){
                            $checkSheet=new Worksheet($excel,'选项');
                            $excel->addSheet($checkSheet);
                        }

                        $items=$v->items ;
                        $formula1='=选项!$'.$cell->col.'$1:$'.$cell->col.'$'.count($items);
                        $prompt='';


                        if(!$checkSheet->getCell($cell->col .'1')->getValue()){
                            foreach ($items as $key=>$val){
                                $checkSheet->getCell($cell->col .($key+1))->setValue($val);
                            }
                        }

                        //设置选项
                        $excel->setActiveSheetIndex(0);
                        $excel->getActiveSheet()->getCell($cell->col . $cell->row)
                            ->getDataValidation()
                            -> setType(DataValidation::TYPE_LIST)
                            -> setErrorStyle(DataValidation::STYLE_INFORMATION)
                            -> setAllowBlank(true)//允许空白
                            -> setShowInputMessage(true)
                            -> setShowErrorMessage(true)
                            -> setShowDropDown(true)
                            -> setErrorTitle('输出错误')
                            -> setError('您输入的值不在下拉框列表内.')
                            -> setPromptTitle('请选择')
                            -> setPrompt($prompt)
                            -> setFormula1($formula1);
//                        dd($cell->value);
                    };
                };
            }

            if($v->group&&$v->group!=='基本信息'){
                isset($groupKeys[$v->group])||$groupKeys[$v->group]=count($expCellName);
                isset($expCellName[$groupKeys[$v->group]])||$expCellName[$groupKeys[$v->group]]=['value'=>$v->group,'childs'=>[]];
                $expCellName[$groupKeys[$v->group]]['childs'][]=$th;
            }else{
                $expCellName[]=$th;
            }
            $row[$v->name]=$v->explain;
        }

        return ['cell'=>$expCellName,'row'=>$row];
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
            $return=" ".$this->excelTilte()
//                ."\n（合并行的单元格，将看成是同样的值；列合并暂不支持）"
            ;
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


    /**
     * 验证导入模板是否合法
     * @param array $data
     * @return array
     * @throws DataNotFoundException
     * @throws DbException
     */
    private function checkExcelTpl(array $data):array{
        ['cell'=>$expCellName,'row'=>$row]=$this->parseExpFields();
        //判断标题是否一致，有些表字段一样的，防止导错
        if(preg_replace('/\s+/','',current($data[1]))!==preg_replace('/\s+/','',$this->getExcelTilte())){
            $this->error('模版错误，请重新下载最新的模版-001');
        }

        $names=[];
        $haveGroup=false;
        foreach ($expCellName as $v) {
            if(empty($v['name'])&&!empty($v['childs'])){
                $haveGroup=true;
                break;
            }
        }

        ksort($data[2]);
        ksort($data[3]);
        if($haveGroup){
            ksort($data[4]);
            foreach ($expCellName as $v) {
                if(empty($v['name'])&&!empty($v['childs'])){
                    if(preg_replace('/\s+/','',current($data[2])) !== preg_replace('/\s+/','',$v['value'])){
                        $this->error('模版错误，请重新下载最新的模版-002');
                    }
                    foreach ($v['childs'] as $val){
                        if (preg_replace('/\s+/','',current($data[3])) !== preg_replace('/\s+/','',$val['value'])||preg_replace('/\s+/','',current($data[4])) !== preg_replace('/\s+/','',$row[$val['name']])) {

                            $this->error('模版错误，请重新下载最新的模版-003');
                        }
                        $names[key($data[3])]=$val['name'];
                        next($data[2]);
                        next($data[3]);
                        next($data[4]);
                    }
                }else{
                    //对比模版，老模版提示错误
                    if (preg_replace('/\s+/','',current($data[2])) !== preg_replace('/\s+/','',$v['value'])||preg_replace('/\s+/','',current($data[4])) !== preg_replace('/\s+/','',$row[$v['name']])) {
                        $this->error('模版错误，请重新下载最新的模版-004');
                    }
                    $names[key($data[2])]=$v['name'];
                    next($data[2]);
                    next($data[3]);
                    next($data[4]);
                }
            }
            unset($data[4]);
        }else{
            foreach ($expCellName as $v) {
                //对比模版，老模版提示错误
                if (preg_replace('/\s+/','',current($data[2])) !== preg_replace('/\s+/','',$v['value'])||preg_replace('/\s+/','',current($data[3])) !== preg_replace('/\s+/','',$row[$v['name']])) {
                    $this->error('模版错误，请重新下载最新的模版-005');
                }
                $names[key($data[2])]=$v['name'];
                next($data[2]);
                next($data[3]);
            }
        }
        //去掉模版上的提示行
        unset($data[1],$data[2], $data[3]);
        if(empty($data)){
            $this->error('未找到可导入的数据');
        }
        return $names;
    }

}