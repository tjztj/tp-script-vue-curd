<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\BetweenFilter;
use tpScriptVueCurd\ModelField;
use think\facade\Validate;
use think\validate\ValidateRule;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

/**两位小数
 * Class DecimalField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class DecimalField extends ModelField
{

    protected float $min = 0.00;//最小值
    protected float $max = 99999999999999.99;//最大值
    protected int $listColumnWidth=110;//设置默认值
    protected string $defaultFilterClass=BetweenFilter::class;
    protected int $precision=2;//数值精度


    /**最小值
     * @param int|null $min
     * @return $this|int
     */
    public function min(int $min = null)
    {
        return $this->doAttr('min', $min);
    }

    /**最大值
     * @param int|null $max
     * @return $this|int
     */
    public function max(int $max = null)
    {
        return $this->doAttr('max', $max);
    }
    /**数值精度
     * @param int|null $precision
     * @return $this|int
     */
    public function precision(int $precision = null)
    {
        return $this->doAttr('precision', $precision);
    }


    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data): self
    {
        $name=$this->name();
        if(isset($data[$name])){
            $data[$name]=trim($data[$name]);
            if($this->required()&&$data[$name]===''){
                throw new \think\Exception('不能为空');
            }

            if($data[$name]){
                $precision=$this->precision();
                $rule=['between'=>$this->min().','.$this->max()];

                if($precision){
                    $rule[]='float';
                    $rule['regex']='/^\-?\d+\.?\d{0,'.$precision.'}$/';
                }else{
                    $rule[]='integer';
                }
                $validate=Validate::rule($name,$rule);
                if(!$validate->check($data)){
                    throw new \think\Exception($validate->getError());
                }
                $this->save=(float)$data[$name];
            }else{
                $this->save=0;
            }
        }else{
            if($this->min>0||$this->max<0){
                $this->defaultCheckRequired('');
            }
        }
        return $this;
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $precision=$this->precision();
        if($precision){
            $excelFieldTpl->explain="填入数字或小数\n小数点精确到后{$precision}位";
        }else{
            $excelFieldTpl->explain="填入整数";
        }
        $excelFieldTpl->width=20;
    }

    public static function getTpl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tp-script-vue-curd-static.php?field/decimal/index.js'),
            new Show($type,'/tp-script-vue-curd-static.php?field/decimal/show.js'),
            new Edit($type,'/tp-script-vue-curd-static.php?field/decimal/edit.js')
        );
    }
}