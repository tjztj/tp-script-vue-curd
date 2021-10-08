<?php


namespace tpScriptVueCurd\option;


use think\Collection;
use think\db\Query;
use tpScriptVueCurd\base\model\BaseModel;

/**
 * Class FunControllerIndexData
 * @property Collection|\think\model\Collection $sourceList
 * @package tpScriptVueCurd\option
 */
class FunControllerIndexData
{

    /**
     * 当前页数，如果为0，代表此列表【$data】不分页
     * @var int
     */
    public int $currentPage = 0;

    /**
     * 数据列表
     * @var array
     */
    public array $data = [];

    /**
     * @var Collection|\think\model\Collection
     */
    public $sourceList;


    /**
     * 总页数，如果为0，代表此列表【$data】不分页
     * @var int
     */
    public int $lastPage = 0;


    /**
     * 每页条数，如果为0，代表此列表【$data】不分页
     * @var int
     */
    public int $perPage = 0;


    /**
     * 数据总数
     * @var int
     */
    public int $total = 0;


    /**
     * 有where条件与排序的model
     * @var Query|null
     */
    public ?Query $model=null;


    /**
     * 如果是子表，且查询的是某一个父下面的子表，才会有
     * @var BaseModel|null
     */
    public ?BaseModel $parentInfo=null;


    /**
     * 其他数据
     * @var array
     */
    public array $other=[];



    public function toArray():array{
        return [
            'current_page'=>$this->currentPage,
            'data'=>$this->data,
            'last_page'=>$this->lastPage,
            'per_page'=>$this->perPage,
            'total'=>$this->total,
            'other'=>$this->other,
        ];
    }
}