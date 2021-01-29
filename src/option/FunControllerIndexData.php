<?php


namespace tpScriptVueCurd\option;


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


    public function toArray():array{
        return [
            'current_page'=>$this->currentPage,
            'data'=>$this->data,
            'last_page'=>$this->lastPage,
            'per_page'=>$this->perPage,
            'total'=>$this->total,
        ];
    }
}