<?php


namespace tpScriptVueCurd\traits\controller;


use think\helper\Str;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use think\Request;
use tpScriptVueCurd\ModelField;

/**
 * Trait CurdFunc
 * @property Request $request
 * @property VueCurlModel $model
 * @package tpScriptVueCurd\traits\controller
 * @author tj 1079798840@qq.com
 */
trait CurdFunc
{


    /**
     * 详情页面
     * @param VueCurlModel $model
     * @param FieldCollection $fields
     * @return mixed|void
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function doShow(VueCurlModel $model,FieldCollection $fields){
        $id=$this->request->param('id/d');
        if(empty($id)){
            return $this->error('缺少必要参数');
        }
        $data=$model->find($id);
        if(empty($data)){
            return $this->error('未找到相关数据信息');
        }
        $info=$data->toArray();
        $fields->doShowData($info);
        $fieldArr=array_values($fields->toArray());


        return $this->showTpl('show',[
            'title'=>$this->model::getTitle(),
            'fields'=>$fieldArr,
            'groupFields'=>$fields->groupItems?FieldCollection::groupListByItems($fieldArr):null,
            'info'=>$info,
        ]);
    }


    /**
     * 获取编辑界面显示需要的参数
     * @param FieldCollection $fields
     * @param VueCurlModel|null $data
     * @return array
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function createEditFetchData(FieldCollection $fields,?VueCurlModel $data){
        if($data){
            $info=$data->toArray();
            //只处理地区
            $fields->filter(fn(ModelField $v)=>in_array($v->name(),[$data::getRegionField(),$data::getRegionPidField()]))->doShowData($info);
            //原信息
            $info['sourceData']=$data;
        }else{
            $info=null;
        }
        $fieldArr=array_values($fields->toArray());
        return [
            'title'=>$this->model::getTitle(),
            'fields'=>$fieldArr,
            'groupFields'=>$fields->groupItems?FieldCollection::groupListByItems($fieldArr):null,
            'info'=>$info,
        ];
    }


    /**
     * 删除时
     * @param VueCurlModel $model
     * @param array $ids
     * @return \think\response\Json|void
     */
    protected function doDelect(VueCurlModel $model,array $ids){
        $ids=array_filter($ids);
        if(empty($ids)){
            return $this->error('请选择要删除的数据');
        }
        try{
            $model->del($ids);
        }catch (\Exception $e){
            return $this->error($e->getMessage());
        }
        return $this->success('删除成功');
    }

    /**
     * 显示模板内容
     * @param $file
     * @param $data
     * @return mixed
     */
    protected function showTpl($file,$data){
        $appName = $this->app->http->getName();
        $view    = $this->app->view->getConfig('view_dir_name');
        $depr =$this->app->view->getConfig('view_depr');

        if (is_dir($this->app->getAppPath() . $view)) {
            $path = $this->app->getAppPath() . $view . DIRECTORY_SEPARATOR;
        } else {
            $path = $this->app->getRootPath() . $view . DIRECTORY_SEPARATOR . ($appName ? $appName . DIRECTORY_SEPARATOR : '');
        }
        $controller = $this->app->request->controller();
        if (strpos($controller, '.')) {
            $pos        = strrpos($controller, '.');
            $controller = substr($controller, 0, $pos) . '.' . Str::snake(substr($controller, $pos + 1));
            $controller_name=Str::snake(substr($controller, $pos + 1));
        } else {
            $controller = Str::snake($controller);
            $controller_name=$controller;
        }
        $template=$file?:Str::snake( $this->app->request->action());
        $path .= str_replace('.', DIRECTORY_SEPARATOR, $controller) . $depr . ($file ?: Str::snake($this->app->request->action())) . '.vue';
        if(file_exists($path)){
            return $this->fetch($controller_name.'/'.$template,$data);
        }
        $tplPath=root_path().'vendor'.DIRECTORY_SEPARATOR.'tj'.DIRECTORY_SEPARATOR.'tp-script-vue-curd'.DIRECTORY_SEPARATOR.'tpl'.DIRECTORY_SEPARATOR;
        return $this->layoutDisplay(file_get_contents($tplPath.'layout'.DIRECTORY_SEPARATOR.'default.vue'),file_get_contents($tplPath.$file.'.vue'),$data);
    }
}