<?php


namespace tpScriptVueCurd\field;
use app\common\constants\RegionConstant;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\RegionFilter;
use tpScriptVueCurd\ModelField;


use app\admin\model\SystemRegion;

/**
 * 地区
 * Class RegionField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class RegionField extends ModelField
{

    /**
     * 地区树型
     * @var array
     */
    protected array $regionTree=[];
    protected string $defaultFilterClass=RegionFilter::class;

    public function __construct(){
        $this->regionTree=SystemRegion::getMyCascaderData();
        $this->listColumnWidth(90);
        parent::__construct();
    }


    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSave(array $data): self
    {
        $name=$this->name();
        if(isset($data[$name])){
            if($this->name==='system_region_pid'){
                $this->save=$this->getSystemRegionPidBySetSave($data[$name],$data);
            }else{
                $val=$data[$name];
                if(is_array($val)){
                    $this->save=end($val);
                }else{
                    $this->save=$val;
                }
            }
        }else{
            if($this->name==='system_region_pid'){
                $this->save=$this->getSystemRegionPidBySetSave('',$data);
            }else{
                $this->save='';
            }
        }
        $this->defaultCheckRequired($this->save);
        return $this;
    }


    private function getSystemRegionPidBySetSave($val,$data){
        if($val===''&&isset($data['system_region_id'])){//如果是镇街
            $system_region_id=0;
            if(is_array($data['system_region_id'])){
                $system_region_id=end($data['system_region_id']);
            }else if(is_numeric($data['system_region_id'])){
                $system_region_id=$data['system_region_id'];
            }
            if($system_region_id){
                return self::getRegionPid($system_region_id);
            }
        }
        return $val;
    }



    /**
     *
     * 显示时要处理的数据
     * @param array $dataBaseData  从数据库中获取的数据
     */
    public function doShowData(array &$dataBaseData): void
    {
        $name=$this->name();
        if(isset($dataBaseData[$name])){
            $dataBaseData[$name]=empty($dataBaseData[$name])?'':self::getRegionName($dataBaseData[$name]);
        }
    }



    public static function getRegionPid(int $system_region_id):string{
        static $regions=null;
        if(is_null($regions)){
            $regions=array_column(SystemRegion::getAll(),'pid','id');
        }
        return $regions[$system_region_id]??'';
    }
    public static function getRegionName(int $system_region_id):string{
        static $regions=null;
        if(is_null($regions)){
            $regions=array_column(SystemRegion::getAll(),'name','id');
        }
        return $regions[$system_region_id]??'';
    }

    /** 可以是 桐君街道 或 阆苑村 或 桐君街道-阆苑村
     * @param string $system_region_name
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getRegionId(string $system_region_name):string{
        static $regions=null;
        if(is_null($regions)){
            foreach (SystemRegion::getAll() as $v){
                $regions[$v['name']]=$v['id'];
                $pName=self::getRegionName($v['pid']);
                if($pName){
                    $regions[$pName.'-'.$v['name']]=$v['id'];
                }
            }
        }
        return $regions[$system_region_name]??'';
    }



    public function getRegionTree(): array
    {
        return $this->regionTree;
    }


    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->explain="填入：镇街-村社,需和\n".url('index/showRegionPage',[],true,true)->build()."\n中的名称对应";
        $excelFieldTpl->width=40;
        $excelFieldTpl->wrapText=true;
    }


    /**
     * EXCEL导入时，对数据的处理
     * @param array $save
     * @throws \think\Exception
     */
    public function excelSaveDoData(array &$save):void{
        if(!isset($save['system_region_id'])){
            return;
        }
        if(!is_numeric($save['system_region_id'])){
            $regions=explode('-',$save['system_region_id']);

            if(empty($regions[0])||empty($regions[1])){
                throw new \think\Exception('填写格式不正确');
            }
            $region_name=$save['system_region_id'];
            $save['system_region_id']=self::getRegionId($region_name);
            if($save['system_region_id']===''){
                throw new \think\Exception('没有找到村社：'.$region_name);
            }
        }
        $save['system_region_pid']=self::getRegionPid($save['system_region_id']);
        if(empty($save['system_region_id'])||empty($save['system_region_pid'])||$save['system_region_pid']==RegionConstant::FIRST_PID){
            throw new \think\Exception('未获取到正确的村社');
        }
        if(self::getRegionPid($save['system_region_id'])!= $save['system_region_pid']){
            throw new \think\Exception('镇街['.$regions[0].']下未找到'.$regions[1]);
        }

    }
}