<?php


namespace tpScriptVueCurd\traits\model;


use think\facade\Db;
use think\Model;
use think\model\Collection;
use tpScriptVueCurd\base\controller\Controller;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\tool\ErrorCode;

/**
 * Trait ModelDelTraits
 * @package tpScriptVueCurd\traits\model
 * @author tj 1079798840@qq.com
 */
trait ModelDelTraits
{

    /**
     * 数据删除方法
     * @param array $ids
     * @return \think\Collection
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final public function del(array $ids): \think\Collection
    {
        $childControllers=$this->controller->getChildControllers();
        if($childControllers){
            foreach ($childControllers as $v){
                /* @var Controller $v */
                $haveChildDataBaseId=(clone $v->md)->where($v->md::parentField(),'in',$ids)->max($v->md::parentField());
                if($haveChildDataBaseId){
                    throw new \think\Exception('需先删除下面的'.$v->title.'数据',ErrorCode::DELETE_HAVE_CHILD);
                }
            }
        }


        return $this->doDel($ids);
    }


    /**
     * 删除验证
     * @param \think\Collection $list
     * @param array $ids
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    final protected function delCheckRowAuth(\think\Collection $list,array $ids): void
    {
        $parents=[];
        if( $this->controller->getParentController()){
            (clone $this->controller->getParentController()->md)->where('id','in',$list->column(static::parentField()))->select()->each(function(BaseModel $v)use(&$parents){
                $parents[$v->id]=$v;
            });
        }

        $fields=$this->fields();
        $list->each(function(self $v)use($fields,$ids,$parents){
            if($v->checkRowAuth($fields,$parents[$v[static::parentField()]]??null,'del')===false){
                throw new \think\Exception('您不能删除第'.(array_search($v->id,$ids)+1).'条数据');
            }
        });
    }


    /**
     * 执行删除，子类可重写
     * @param $ids
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    /**
     * @param $ids
     * @return \think\Collection
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    protected function doDel($ids):\think\Collection{
        $list=$this->where('id','in',array_unique($ids))->select();
        $this->delCheckRowAuth($list,$ids);
        $this->onDelBefore($list);

        $modelFields=[];
        $modelIds=[];
        $user=tpScriptVueCurdGetLoginData();
        foreach ($list as $result){
            /**
             * @var $result Model
             */
            //设置当前删除人
            if($user&&!empty($user['id'])){
                $class=get_class($result);
                isset($modelFields[$class])||$modelFields[$class]=$result->getFields();
                if(isset($modelFields[$class]['delete_system_admin_id'])){
                    isset($modelIds[$class])||$modelIds[$class]=[];
                    $modelIds[$class][]=$result->id;
                }
            }
            $result->delete();
        }
        foreach ($modelIds as $md=>$mdIds){
            Db::name((new $md)->getName())->whereIn('id',$mdIds)->update(['delete_system_admin_id'=>$user['id']]);
        }
        $this->onDelAfter($list);
        return $list;
    }



    protected function onDelBefore(Collection $delList): void{}//删除前钩子，子类重写
    protected function onDelAfter(Collection $delList): void{}//删除后钩子，子类重写
}