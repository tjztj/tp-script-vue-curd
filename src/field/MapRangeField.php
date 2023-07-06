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

class MapRangeField extends ModelField
{
    protected string $defaultFilterClass=EmptyFilter::class;
    protected bool $canExcelImport=false;//是否可以excel导入
    protected LngLat $center;//默认中心坐标
    //行政区域、范围，如：浙江省杭州市萧山区河上镇
    protected string $district='';

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


    /**行政区域、范围，如：杭州市、萧山区、衢江区
     * 行政区级别包括：国家、省/直辖市、市、区/县4个级别
     * @param string|null $district
     * @return $this|string
     */
    public function district(string $district = null)
    {
        return $this->doAttr('district', $district);
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

    //GCJ-02(火星，高德) 坐标转换成 BD-09(百度) 坐标//@param gg_lon 火星经度//@param gg_lat 火星纬度
    public static function bdEncrypt($gg_lon,$gg_lat)
    {
        $x_pi = 3.14159265358979324 * 3001.08 / 180.0;
        $x = $gg_lon;
        $y = $gg_lat;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $data['lon'] = $z * cos($theta) + 0.0065;
        $data['lat'] = $z * sin($theta) + 0.006;
//        $data['lon'] = $z * cos($theta) + 0.00685;
//        $data['lat'] = $z * sin($theta) + 0.00536;
        return$data;
    }
    //BD-09(百度) 坐标转换成  GCJ-02(火星，高德) 坐标//@param bd_lon 百度经度//@param bd_lat 百度纬度
    public static function bdDecrypt($bd_lon,$bd_lat)
    {
        $x_pi = 3.14159265358979324 * 3000.0 / 180.0;
        $x = $bd_lon - 0.0065;
        $y = $bd_lat - 0.006;
        $z = sqrt($x * $x + $y * $y) - 0.00002 * sin($y * $x_pi);
        $theta = atan2($y, $x) - 0.000003 * cos($x * $x_pi);
        $data['lon'] = $z * cos($theta);
        $data['lat'] = $z * sin($theta);
        return$data;
    }

    /**
     * 将字段的值由高德坐标转换为百度地图的坐标
     * @param string|array $jsonOrArr
     * @return array
     */
    public static function getRangPointToBaidu($jsonOrArr):array{
        if(!$jsonOrArr){
            return [];
        }
        if(is_string($jsonOrArr)){
            $val=json_decode($jsonOrArr,true);
            if(!$val){
                return [];
            }
        }else{
            $val= $jsonOrArr;
        }
        $newValues=[];
        foreach ($val as $v){
            $val=self::bdEncrypt($v[0],$v[1]);
            $newValues[]=[$val['lon'],$val['lat']];
        }
        return $newValues;
    }


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
     * @param bool $canExcelImport
     */
    public function setCanExcelImport(bool $canExcelImport): void
    {
        if($canExcelImport===true){
            throw new \think\Exception('此字段不可导入');
        }
        $this->canExcelImport = $canExcelImport;
    }

    public function listShow(bool $listShow = null)
    {
        if(is_null($listShow)){
            return $this->listShow;
        }
        if($listShow===true){
            throw new \think\Exception('此字段不可在列表中显示');
        }
        return $this;
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,'/tpscriptvuecurd/field/map_range/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/map_range/edit.js')
        );
    }

    /**
     * 数据库字段生成
     * @param GenerateColumnOption $option
     * @return void
     */
    public function getGenerateColumnConfig(GenerateColumnOption $option): void
    {
        $option->setTypeText();
    }
}