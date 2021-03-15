<?php


namespace tpScriptVueCurd\traits\model;


use think\model\Collection;

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
     */
    abstract public function del(array $ids):\think\Collection;
    protected function onDelBefore(Collection $delList): void{}//删除前钩子，子类重写
    protected function onDelAfter(Collection $delList): void{}//删除后钩子，子类重写


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
        $this->onDelBefore($list);
        foreach ($list as $result){$result->delete();}
        $this->onDelAfter($list);
        return $list;
    }
}