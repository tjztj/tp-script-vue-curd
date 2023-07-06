<?php

namespace tpScriptVueCurd\field;

use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\EmptyFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\field\coordinate\LngLat;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class CoordinateField extends ModelField
{
    protected string $defaultFilterClass=EmptyFilter::class;
    protected bool $canExcelImport=false;//是否可以excel导入
    protected LngLat $center;//默认中心坐标

    protected string $mapType='TMap';//'TMap','AMap','BMapGL'


    protected int $zIndex=0;

    public function __construct()
    {
        parent::__construct();
        //设置默认值
        $this->center=new LngLat(120.19,30.19);
    }


    /**地图类型  'TMap','AMap','BMapGL'
     * @param string $mapType
     * @return $this|LngLat
     */
    public function mapType(string $mapType = null)
    {
        if($mapType!==null){
            if(!in_array($mapType,['TMap','AMap','BMapGL'])){
                throw new \think\Exception('地图类型只能是（TMap,AMap,BMapGL），当前传入的值为'.$mapType);
            }
        }
        return $this->doAttr('mapType', $mapType);
    }


    /**地图中心坐标(https://map.tianditu.gov.cn/)
     * @param LngLat|null $center
     * @return $this|LngLat
     */
    public function center(LngLat $center = null)
    {
        return $this->doAttr('center', $center);
    }


    /**
     * 设置/获取z-index
     * @param int|null $zIndex
     * @return MapRangeField|int
     */
    public function zIndex(int $zIndex = null)
    {
        return $this->doAttr('zIndex', $zIndex);
    }


    /**
     * 设置保存的值
     * @param array $data
     * @param BaseModel $old
     * @return ModelField
     * @throws \think\Exception
     */
    protected function setSaveVal(array $data, BaseModel $old): ModelField
    {
        if(isset($data[$this->name()])){
            $this->save=trim($data[$this->name()]);
            $this->defaultCheckRequired($this->save);
        }else{
            $this->defaultCheckRequired('');
        }
        return $this;
    }


    /**
     * 导入模板处理
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->explain='填入坐标';

    }



    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,'/tpscriptvuecurd/field/coordinate/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/coordinate/edit.js')
        );
    }

    /**
     * 数据库字段生成配置
     * @param  GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option):void{
        $option->setTypeVarchar();
    }
}