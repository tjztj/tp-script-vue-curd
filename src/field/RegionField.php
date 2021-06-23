<?php


namespace tpScriptVueCurd\field;

use app\common\constants\RegionConstant;
use tpScriptVueCurd\base\model\VueCurlModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\RegionFilter;
use tpScriptVueCurd\ModelField;


use app\admin\model\SystemRegion;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\NumHideFields;

/**
 * 地区
 * Class RegionField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class RegionField extends ModelField
{

    use NumHideFields;
    /**
     * 地区树型
     * @var array
     */
    protected array $regionTree = [];
    protected string $defaultFilterClass = RegionFilter::class;
    protected bool $canEdit=false;//编辑页面是否可修改
    protected bool $canCheckParent=false;//是否可选中父级
    protected bool $multiple=false;//是否多选

    protected string $pField = '';//父字段名
    protected string $cField = '';//子字段名

    protected $nullVal=0;//字段在数据库中为空时的值



    public function __construct($otherConfig)
    {
        $this->regionTree = $otherConfig?:SystemRegion::getMyCascaderData();
        $this->listColumnWidth(90);
        parent::__construct();
    }

    /**父字段名
     * @param string|null $pField
     * @return $this|string
     */
    public function pField(string $pField = null)
    {
        if (!is_null($pField)) {
            if ($this->cField() === '' && $this->name() !== $pField) {
                $this->cField($this->name());
            }
        }
        return $this->doAttr('pField', $pField);
    }

    /**父字段名
     * @param bool|null $canCheckParent
     * @return $this|string
     */
    public function canCheckParent(bool $canCheckParent = null)
    {
        return $this->doAttr('canCheckParent', $canCheckParent);
    }


    /**
     * 是否可多选
     * @param bool $multiple
     * @return RegionField|bool
     */
    public function multiple(bool $multiple=null){
         return $this->doAttr('multiple',$multiple);
    }



    /**
     * 编辑页面是否可修改
     * @param bool|null $canEdit
     * @return RegionField|bool
     */
    public function canEdit(bool $canEdit=null){
        return $this->doAttr('canEdit',$canEdit);
    }

    /**子字段名
     * @param string|null $cField
     * @return $this|string
     */
    public function cField(string $cField = null)
    {
        if (!is_null($cField)) {
            if ($this->pField() === '' && $this->name() !== $cField) {
                $this->pField($this->name());
            }
        }
        return $this->doAttr('cField', $cField);
    }


    /**
     * 设置保存的值
     * @param array $data 数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        if($this->multiple){
            $name = $this->name();
            if (isset($data[$name])) {
                $val = $data[$name];
                if (is_array($val)) {
                    $this->save = implode(',',$val);
                } else {
                    $this->save = $val;
                }
            } else {
                $this->save = $this->nullVal();
            }

        }else{
            if ($this->pField() === '') {
                throw new \think\Exception('地区字段未配置 pField');
            }

            $name = $this->name();
            if (isset($data[$name])) {
                if ($this->name === $this->pField()) {
                    $this->save = $this->getSystemRegionPidBySetSave($data[$name], $data);
                } else {
                    $val = $data[$name];
                    if (is_array($val)) {
                        $this->save = end($val);
                    } else {
                        $this->save = $val;
                    }
                }
            } else {
                if ($this->name === $this->pField()) {
                    $this->save = $this->getSystemRegionPidBySetSave('', $data);
                } else {
                    $this->save = $this->nullVal();
                }
            }
        }
        $this->defaultCheckRequired($this->save);
        return $this;
    }



    private function getSystemRegionPidBySetSave($val, $data)
    {
        if ($this->cField() === '') {
            throw new \think\Exception('地区字段未配置 cField');
        }

        if (isset($data[$this->cField()])) {//如果是镇街
            $cRegionId = 0;
            if (is_array($data[$this->cField()])) {
                $cRegionId = end($data[$this->cField()]);
            } else if (is_numeric($data[$this->cField()])) {
                $cRegionId = $data[$this->cField()];
            }
            if ($cRegionId) {
                return self::getRegionPid($cRegionId);
            }
        }
        return $val;
    }


    /**
     *
     * 显示时要处理的数据
     * @param array $dataBaseData 从数据库中获取的数据
     */
    public function doShowData(array &$dataBaseData): void
    {
        $name = $this->name();
        if (isset($dataBaseData[$name])) {
            if( empty($dataBaseData[$name])){
                $dataBaseData[$name]=$this->getTreeToList()[$dataBaseData[$name]]['label']??'';
                return;
            }

            if(!$this->multiple){
                $dataBaseData[$name] = $this->getTreeToList()[$dataBaseData[$name]]['label']??self::getRegionName($dataBaseData[$name]);
                return;
            }

            $arr=is_array($dataBaseData[$name])?$dataBaseData[$name]:explode(',',$dataBaseData[$name]);

            foreach ($arr as $k=>$v){
                $arr[$k]=self::getRegionName($v)?:$v;
            }
            $dataBaseData[$name]=implode('，',$arr);
        }
    }


    /**
     * 获取当前选项的列表
     * @return array
     */
    public function getTreeToList(){
        static $list=[];
        $func=function($tree)use(&$list,&$func){
            foreach ($tree as $v){
                $list[$v['value']]=$v;
                if(!empty($v['children'])){
                    $func($v['children']);
                }
            }
        };
        if(empty($list)){
            $func($this->regionTree);
        }
        return $list;
    }


    public static function getRegionPid(int $cRegionId): string
    {
        static $regions = null;
        if (is_null($regions)) {
            $regions = array_column(SystemRegion::getAll(), 'pid', 'id');
        }
        return $regions[$cRegionId] ?? '';
    }

    public static function getRegionName(int $cRegionId): string
    {
        static $regions = null;
        if (is_null($regions)) {
            $regions = array_column(SystemRegion::getAll(), 'name', 'id');
        }
        return $regions[$cRegionId] ?? '';
    }

    /** 可以是 桐君街道 或 阆苑村 或 桐君街道-阆苑村
     * @param string $region_name
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public static function getRegionId(string $region_name): string
    {
        static $regions = null;
        if (is_null($regions)) {
            foreach (SystemRegion::getAll() as $v) {
                $regions[$v['name']] = $v['id'];
                $pName = self::getRegionName($v['pid']);
                if ($pName) {
                    $regions[$pName . '-' . $v['name']] = $v['id'];
                }
            }
        }
        return $regions[$region_name] ?? '';
    }


    public function getRegionTree(): array
    {
        return $this->regionTree;
    }

    public function setRegionTree(array $RegionTree):self{
        $this->regionTree=$RegionTree;
        return $this;
    }


    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl): void
    {
        $excelFieldTpl->explain = "填入：镇街-村社,需和\n" . url('index/showRegionPage', [], true, true)->build() . "\n中的名称对应";
        $excelFieldTpl->width = 40;
        $excelFieldTpl->wrapText = true;
    }


    /**
     * EXCEL导入时，对数据的处理
     * @param array $save
     * @throws \think\Exception
     */
    public function excelSaveDoData(array &$save): void
    {
        if ($this->cField() === '') {
            throw new \think\Exception('地区字段未配置 cField');
        }

        if ($this->pField() === '') {
            throw new \think\Exception('地区字段未配置 pField');
        }

        if (!isset($save[$this->cField()])) {
            return;
        }

        if($this->pField()===$this->name()){
            return;
        }

        if (!is_numeric($save[$this->cField()])) {
            $regions = explode('-', $save[$this->cField()]);

            if($this->canCheckParent()&&count($regions)===1){
                $save[$this->cField()]=self::getRegionId($regions[0]);
                if(!empty(self::getRegionPid($save[$this->cField()]))){
                    throw new \think\Exception('填写格式不正确');
                }
                $save[$this->pField()]=0;
                return;
            }


            if (empty($regions[0]) || empty($regions[1])) {
                throw new \think\Exception('填写格式不正确');
            }

            $save[$this->cField()] = self::getRegionId($regions[1]);
            if ($save[$this->cField()] === '') {
                throw new \think\Exception('没有找到村社：' . $regions[1]);
            }
        }
        $save[$this->pField()] = self::getRegionPid($save[$this->cField()]);

        if(empty($save[$this->cField()]) || empty($save[$this->pField()]) ){
            throw new \think\Exception('未获取到正确的村社-001');
        }
        if(!$this->canCheckParent()&&$save[$this->pField()] == RegionConstant::FIRST_PID){
            throw new \think\Exception('未获取到正确的村社-002');
        }

        if (self::getRegionPid($save[$this->cField()]) != $save[$this->pField()]) {
            throw new \think\Exception('镇街[' . self::getRegionName($save[$this->pField()]) . ']下未找到' . self::getRegionName($save[$this->cField()]));
        }

    }


    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,''),
            new Show($type,''),
            new Edit($type,'/tp-script-vue-curd-static.php?field/region/edit.js')
        );
    }
}