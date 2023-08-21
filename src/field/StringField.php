<?php


namespace tpScriptVueCurd\field;
use tpScriptVueCurd\base\model\BaseModel;
use tpScriptVueCurd\ExcelFieldTpl;
use tpScriptVueCurd\filter\LikeFilter;
use tpScriptVueCurd\ModelField;
use tpScriptVueCurd\option\generate_table\GenerateColumnOption;
use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;
use tpScriptVueCurd\traits\field\ListEdit;


/**
 * 短字符串
 * Class StringField
 * @author tj 1079798840@qq.com
 * @package tpScriptVueCurd
 */
class StringField extends ModelField
{
    use ListEdit;
    protected string $defaultFilterClass=LikeFilter::class;

    protected bool $readOnlyJustShowText=false;


    protected bool $disengageSensitivity=false;

    /**
     * min before after
     * @var string|null
     */
    protected ?string $disengageSensitivityFormat=null;

    /**
     * readOnly时，是否不显示输入框
     * @param bool|null $readOnlyJustShowText
     * @return $this|bool
     */
    public function readOnlyJustShowText(bool $readOnlyJustShowText = null)
    {
        return $this->doAttr('readOnlyJustShowText', $readOnlyJustShowText);
    }


    /**
     * 脱敏
     * @param bool|null $disengageSensitivity
     * @param string|null $format min before after aaa***bbb
     * @return $this|bool
     */
    public function disengageSensitivity(bool $disengageSensitivity = null,string $format=null)
    {
        if($format!==null){
            $this->disengageSensitivityFormat=$format;
        }
        return $this->doAttr('disengageSensitivity', $disengageSensitivity);
    }

    /**
     * 设置保存的值
     * @param array $data  数据值集合
     * @return $this
     */
    public function setSaveVal(array $data,BaseModel $old): self
    {
        if(isset($data[$this->name()])){
            $this->save=trim($data[$this->name()]);
            if($this->disengageSensitivity()&&!empty($old->id)&&$this->save===self::tuoMin($old[$this->name()],$this->disengageSensitivityFormat)){
                $this->save=$old[$this->name()];
            }

            if($this->required()&&($this->save===''||$this->save===$this->nullVal())){
                $this->defaultCheckRequired($this->save);
            }
        }else{
            $this->defaultCheckRequired('');
        }
        return $this;
    }

    /**
     * 显示时要处理的数据
     * @param array $dataBaseData
     */
    public function doShowData(array &$dataBaseData): void
    {
        $name=$this->name();
        if(isset($dataBaseData[$name])&&$this->disengageSensitivity()){
            $dataBaseData[$name]=self::tuoMin($dataBaseData[$name],$this->disengageSensitivityFormat);
        }
    }


    public function getDisengageSensitivityFormat():string{
        return $this->disengageSensitivityFormat;
    }

    /**
     * 模板导入备注
     * @param ExcelFieldTpl $excelFieldTpl
     * @return void
     */
    public function excelTplExplain(ExcelFieldTpl $excelFieldTpl):void{
        $excelFieldTpl->width=22;
    }

    public static function componentUrl(): FieldTpl
    {
        $type=class_basename(static::class);
        return new FieldTpl($type,
            new Index($type,'/tpscriptvuecurd/field/string/index.js'),
            new Show($type,''),
            new Edit($type,'/tpscriptvuecurd/field/string/edit.js')
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

    /**
     * 脱敏处理
     * @param string $val
     * @param string|null $disengageSensitivityFormat
     * @return string
     */
    public static function tuoMin(string $val,string $disengageSensitivityFormat=null): string
    {
        $len=mb_strlen($val);
        if($len===0){
            return '';
        }


        $getDefReturn= static function ($beforeLen, $afterLen, $format)use($len,$val){
            if($len<2){
                return '*';
            }
            if($len<3){
                if($format!=='after'){
                    $afterLen=is_null($afterLen)?1:$afterLen;
                    return $afterLen?'*'.mb_substr($val,-$afterLen):str_pad('',$len,'*');
                }
                $beforeLen=is_null($beforeLen)?1:$beforeLen;
                if(!$afterLen){
                    return $beforeLen?mb_substr($val,0,$beforeLen).'*':str_pad('',$len,'*');
                }
                return str_pad('',$len,'*');
            }

            if($len<4){
                $beforeLen=is_null($beforeLen)?1:$beforeLen;
                $afterLen=is_null($afterLen)?1:$afterLen;
                $asterisk=str_pad('',$len-$beforeLen-$afterLen,'*');
                if($format==='before'){
                    return $afterLen?$asterisk.mb_substr($val,-$afterLen):str_pad('',$len,'*');
                }

                if($format==='after'){
                    return $beforeLen?mb_substr($val,0,$beforeLen).$asterisk:str_pad('',$len,'*');
                }
                return mb_substr($val,0,$beforeLen).$asterisk.($afterLen?mb_substr($val,-$afterLen):str_pad('',$len-$beforeLen,'*'));
            }

            if($len<5){
                $beforeLen=is_null($beforeLen)?1:$beforeLen;
                $afterLen=is_null($afterLen)?1:$afterLen;
            }else if($len<7){
                $beforeLen=is_null($beforeLen)?1:$beforeLen;
                $afterLen=is_null($afterLen)?1:$afterLen;
            }else if($len<8){
                $beforeLen=is_null($beforeLen)?1:$beforeLen;
                $afterLen=is_null($afterLen)?2:$afterLen;
            }else{
                $beforeLen=is_null($beforeLen)?2:$beforeLen;
                $afterLen=is_null($afterLen)?2:$afterLen;
            }
            if($format==='before'){
                return $afterLen?'***'.mb_substr($val,-$afterLen):str_pad('',$len,'*');
            }

            if($format==='after'){
                return $beforeLen?mb_substr($val,0,$beforeLen).'***':str_pad('',$len,'*');
            }
            return mb_substr($val,0,$beforeLen).'***'.($afterLen?mb_substr($val,-$afterLen):str_pad('',$len-$beforeLen,'*'));
        };


        if(is_null($disengageSensitivityFormat)&&filter_var($val,FILTER_VALIDATE_EMAIL)){
            switch (mb_strrpos($val, '@')){
                case 1:
                    $beforeLen=0;
                    break;
                case 2:
                case 3:
                    $beforeLen=1;
                    break;
                case 4:
                case 5:
                    $beforeLen=2;
                    break;
                default:
                    $beforeLen=3;
            }
            return $getDefReturn($beforeLen,mb_strlen($val)-mb_strrpos($val, '@'),'min');
        }
        $format=$disengageSensitivityFormat;
        if(is_null($format)){
            return $getDefReturn(null,null,'min');
        }
        if(in_array($format,['before','after','min'])){
            return $getDefReturn(null,null,$format);
        }

        $val=str_replace(['a','b'],['α','β'],$val);
        $formatArr=preg_split('//u', $format, -1, PREG_SPLIT_NO_EMPTY);
        $beforeArr=preg_split('//u',$val, -1, PREG_SPLIT_NO_EMPTY);


        $newBefore=[];
        $beforeI=0;

        if($len<=mb_substr_count($format,'a')+mb_substr_count($format,'b')){
            $maxBeforeI=ceil($len/count($formatArr)*mb_substr_count($format,'a'));
            $maxAfterI=ceil($len/count($formatArr)*mb_substr_count($format,'b'));
            if($maxBeforeI+$maxAfterI>=$len){
                if($maxBeforeI+$maxAfterI-$len===2){
                    $maxBeforeI--;
                    $maxAfterI--;
                }else if($maxAfterI>$maxBeforeI){
                    $maxAfterI--;
                }else{
                    $maxBeforeI--;
                }
            }
        }else{
            $maxBeforeI=$maxAfterI=$len+1;
        }



        foreach ($formatArr as $v){
            if($v==='a'&&$beforeI<$maxBeforeI){
                $newBefore[]=$beforeArr[$beforeI]??'';
                $beforeI++;
            }else{
                $newBefore[]=$v==='a'?'':$v;
            }
        }


        $afterArr=array_reverse($beforeArr);
        $endI=0;
        $new=[];
        foreach (array_reverse($newBefore) as $v){
            if($v==='b'&&$endI<$maxAfterI){
                $new[]=$afterArr[$endI]??'';
                $endI++;
            }else{
                $new[]=$v==='b'?'':$v;
            }
        }
        $newVal=implode('',array_reverse($new));
        if($beforeI+$endI<count($formatArr)&&$newVal!==$val){
            return str_replace(['α','β'],['a','b'],$newVal);
        }



        return str_replace(['a','b'],'',$format);
    }

}