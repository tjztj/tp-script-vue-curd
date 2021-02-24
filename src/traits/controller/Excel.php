<?php


namespace tpScriptVueCurd\traits\controller;



use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\FieldCollection;
use tpScriptVueCurd\ModelField;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use think\Request;
use tpScriptVueCurd\option\FunControllerImportAfter;
use tpScriptVueCurd\option\FunControllerImportBefore;

/**
 * Trait Excel
 * @property Request $request
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait Excel
{

    public VueCurlModel $model;
    public FieldCollection $fields;




    /**
     * 导入关键参数(字段集合)，可继承然后重写
     * @return FieldCollection
     */
    protected function excelFields():FieldCollection{
        return $this->fields;
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
        return static::getTitle();
    }


    /**
     * 执行添加逻辑，可继承然后重写
     * @param $saveData
     * @return VueCurlModel
     */
    protected function excelSave($saveData):VueCurlModel{
        static $modelClassName;
        if(!isset($modelClassName)){
            $modelClassName=get_class($this->model);
        }

        $option=new FunControllerImportBefore();
        $option->saveArr=$saveData;
        $this->importBefore($option);
        $info=(new $modelClassName)->addInfo($option->saveArr,null,true);

        $optionAfter=new FunControllerImportAfter();
        $optionAfter->saveObjects=$info;
        $this->importAfter($optionAfter);

        return $info;
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
            return $this->error('未获取到上传文件');
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
            return $this->error($e->getMessage());
        }



        ['expCellName'=>$expCellName,'row'=>$row]=$this->parseExpFields();
        //判断标题是否一致，有些表字段一样的，防止导错
        if(trim(current($data[1]))!==trim($this->getExcelTilte())){
            return $this->error('模版错误，请重新下载最新的模版-001');
        }


        $names=[];
        foreach ($expCellName as $k => $v) {
            //对比模版，老模版提示错误
            if (current($data[2]) !== $v[1]||trim(current($data[3])) !== trim($row[$v[0]])) {
                return $this->error('模版错误，请重新下载最新的模版-002');
            }
            $names[key($data[2])]=$v[0];
            next($data[2]);
            next($data[3]);
        }
        //去掉模版上的提示行
        unset($data[1],$data[2], $data[3]);
        if(empty($data)){
            return $this->error('未找到可导入的数据');
        }


        $this->model->startTrans();
        $last_do_row=4;
        try{
            //因为$data排序已经乱了，所以我用while遍历
            while (isset($data[$last_do_row])){
                $saveData=[];
                $isEmptyRow=true;
                foreach ($data[$last_do_row] as $key=>$val){
                    if(!isset($names[$key])){
                        throw new \think\Exception('模板错误');
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
            $this->model->rollback();
            return $this->error('Excel第'.$last_do_row.'行 '.$e->getMessage());
        }

        $this->model->commit();
        return $this->success('导入成功');
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
    function downExcelTpl():void{
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
     * 获取excel头部字段配置
     * @return ExcelFieldTpl[]
     */
    private function getTHeadExpFields():array{
        $expFields = [];
        $this->getExcelFields()->each(function(ModelField $v)use(&$expFields){
            if(!$v->editShow()||!$v->canExcelImport()){
                return ;
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