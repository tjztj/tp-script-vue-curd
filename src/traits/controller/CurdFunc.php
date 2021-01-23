<?php


namespace tpScriptVueCurd\traits\controller;


use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\FieldCollection;
use think\Request;

/**
 * Trait CurdFunc
 * @property Request $request
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

        return $this->fetch('vuecurd/show',[
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
            $fields->filter(fn($v)=>$v->name()==='system_region_id'|| $v->name()==='system_region_pid')->doShowData($info);
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
}