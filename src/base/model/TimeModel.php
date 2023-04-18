<?php


namespace tpScriptVueCurd\base\model;


use think\Model;
use think\model\concern\SoftDelete;
use tpScriptVueCurd\traits\Func;

/**
 * 有关时间的模型
 * Class TimeModel
 * @author tj 1079798840@qq.com
 */
class TimeModel extends Model
{

    protected $readonly = ['create_time'];
    /**
     * 自动时间戳类型
     * @var string
     */
    protected $autoWriteTimestamp = true;

    /**
     * 添加时间
     * @var string
     */
    protected $createTime = 'create_time';

    /**
     * 更新时间
     * @var string
     */
    protected $updateTime = 'update_time';

    /**
     * 软删除
     */
    use Func,SoftDelete;
    protected $deleteTime = 'delete_time';
    protected $defaultSoftDelete = 0;//软删除默认值

}