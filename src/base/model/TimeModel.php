<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------


namespace tpScriptVueCurd\base\model;


use think\Model;
use think\model\concern\SoftDelete;
use tpScriptVueCurd\traits\Func;

/**
 * 有关时间的模型
 * Class TimeModel
 * @author tj 1079798840@qq.com
 * @package app\common\model
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