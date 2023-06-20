<?php

namespace tpScriptVueCurd\traits\field_where;

trait FieldWhereDump
{

    /**
     * 获取当前不包括and与or的where结构
     * @param bool $dumpIsHtml
     * @return string
     */
    private function getDump(bool $dumpIsHtml=false):string{
        $name=$this->field->name();
        if($dumpIsHtml){
            $name='<span style="color:#006d75;border-bottom: 1px solid #36cfc9">'.$name.'</span>';
        }
        if($this->type===self::TYPE_IN){
            if(count($this->valueData)>1){
                if($dumpIsHtml){
                    return $name.' '.($this->isNot?'<b style="color: #c41d7f">NOT IN</b>':'<b style="color: #c41d7f">IN</b>').' <b style="color: #c41d7f">("</b><span style="color: #8c8c8c">'.implode('</span><b style="color: #c41d7f">","</b><span style="color: #8c8c8c">',$this->valueData).'</span><b style="color: #c41d7f">")</span>';
                }
                return $name.' '.($this->isNot?'NOT IN':'IN').' ("'.implode('","',$this->valueData).'")';
            }
            if($dumpIsHtml){
                return $name.' '.($this->isNot?'<b style="color: #c41d7f"><></b>':'<b style="color: #c41d7f">=</b>').' <b style="color: #c41d7f">"</b><span style="color: #8c8c8c">'.current($this->valueData).'</span><b style="color: #c41d7f">"</b>';
            }
            return $name.' '.($this->isNot?'<>':'=').' "'.current($this->valueData).'"';

        }
        if($this->type===self::TYPE_FIND_IN_SET){
            if($this->isNot){
                $sqls=[];
                foreach ($this->valueData as $v){
                    if($dumpIsHtml){
                        $sqls[]='<b style="color: #c41d7f">FIND_IN_SET("</b><span style="color: #8c8c8c">'.addslashes($v).'</span><b style="color: #c41d7f">",</b>'.$name.'<b style="color: #c41d7f">)</b>';
                    }else{
                        $sqls[]='FIND_IN_SET("'.addslashes($v).'",'.$name.')';
                    }

                }
                if(count($sqls)>0){
                    if($dumpIsHtml){
                        return '<b style="color: #c41d7f">NOT (</b>'.implode(' <b style="color: #c41d7f">OR</b> ',$sqls).'<b style="color: #c41d7f">)</b>';
                    }
                    return 'NOT ('.implode(' OR ',$sqls).')';
                }
                if($dumpIsHtml){
                    return '<b style="color: #c41d7f">NOT</b> '.current($sqls);
                }
                return 'NOT '.current($sqls);

            }
            $sqls=[];
            foreach ($this->valueData as $v){
                if($dumpIsHtml){
                    $sqls[]='<b style="color: #c41d7f">FIND_IN_SET("</b><span style="color: #8c8c8c">'.addslashes($v).'</span><b style="color: #c41d7f">",</b>'.$name.'<b style="color: #c41d7f">)</b>';
                }else{
                    $sqls[]='FIND_IN_SET("'.addslashes($v).'",'.$name.')';
                }
            }
            if($dumpIsHtml){
                $sqlStr=implode(' <b style="color: #c41d7f">OR</b> ',$sqls);
            }else{
                $sqlStr=implode(' OR ',$sqls);
            }

            if(count($sqls)>1){
                if($dumpIsHtml){
                    return '<b style="color: #c41d7f">(</b>'.$sqlStr.'<b style="color: #c41d7f">)</b>';
                }
                return '('.$sqlStr.')';
            }
            return $sqlStr;
        }

        if(is_null($this->valueData[0])){
            if($dumpIsHtml){
                return $name.' '.($this->isNot?'<b style="color: #c41d7f">&gt;</b>':'<b style="color: #c41d7f">&lt;=</b>').'<b style="color: #c41d7f">"</b><span style="color: #8c8c8c">'.$this->valueData[1].'</span><b style="color: #c41d7f">"</b>';
            }
            return $name.' '.($this->isNot?'>':'<=').'"'.$this->valueData[1].'"';
        }

        if(is_null($this->valueData[1])){
            if($dumpIsHtml){
                return $name.' '.($this->isNot?'<b style="color: #c41d7f">&lt;</b>':'<b style="color: #c41d7f">&gt;=</b>').'<b style="color: #c41d7f">"</b><span style="color: #8c8c8c">'.$this->valueData[0].'</span><b style="color: #c41d7f">"</b>';
            }
            return $name.' '.($this->isNot?'<':'>=').'"'.$this->valueData[0].'"';
        }

        if($this->isNot){
            if($dumpIsHtml){
                return '<b style="color: #c41d7f">(</b>'.$name.' <b style="color: #c41d7f">&lt;"</b><span style="color: #8c8c8c">'.$this->valueData[0].'</span><b style="color: #c41d7f">" AND</b> '.$name.' <b style="color: #c41d7f">&gt; "</b><span style="color: #8c8c8c">'.$this->valueData[1].'</span><b style="color: #c41d7f">")</b>';
            }
            return '('.$name.' <"'.$this->valueData[0].'" AND '.$name.' > "'.$this->valueData[1].'")';
        }
        if($dumpIsHtml){
            return '<b style="color: #c41d7f">(</b>'.$name.' <b style="color: #c41d7f">&gt;="</b><span style="color: #8c8c8c">'.$this->valueData[0].'</span><b style="color: #c41d7f">" AND</b> '.$name.' <b style="color: #c41d7f">&lt;= "</b><span style="color: #8c8c8c">'.$this->valueData[1].'</span><b style="color: #c41d7f">")</b>';
        }
        return '('.$name.' >="'.$this->valueData[0].'" AND '.$name.' <= "'.$this->valueData[1].'")';
    }


    /**
     * 打印条件
     * @param bool $dumpIsHtml
     */
    public function dump(bool $dumpIsHtml=true):void{
        echo $this->dumpStr($dumpIsHtml);

    }


    /**
     * 获取条件数组结构，方便打印
     * @param bool $dumpIsHtml
     * @return array[]
     */
    public function dumpArr(bool $dumpIsHtml=false):array{
        $ands=[];
        if($this->field->name()!==self::RETURN_FALSE_FIELD_NAME){
            $ands[]=$this->getDump($dumpIsHtml);
        }
        foreach ($this->ands as $v){
            $ands[]=$v->dumpArr($dumpIsHtml);
        }

        $ors=[];
        foreach ($this->ors as $v){
            $ors[]=$v->dumpArr($dumpIsHtml);
        }
        return ['ands'=>$ands,'ors'=>$ors];
    }


    /**
     * 获取当前where结构展示
     * @param bool $dumpIsHtml 是否以HTML格式输出
     * @return string
     */
    public function dumpStr(bool $dumpIsHtml=false):string{
        $tree=$this->dumpArr($dumpIsHtml);

        $getChildsOne=function ($info)use(&$getChildsOne){
            if(empty($info['ands'])&&count($info['ors'])===1&&is_array(current($info['ors']))){
                return $getChildsOne(current($info['ors']));
            }
            if(empty($info['ors'])&&count($info['ands'])===1&&is_array(current($info['ands']))){
                return $getChildsOne(current($info['ands']));
            }
            return $info;
        };
        $getChildHtml=function ($info,$level=0,$parent=null,$parentType='')use(&$getChildHtml,$getChildsOne,$dumpIsHtml){
            $info=$getChildsOne($info);

            $andCount=count($info['ands']);
            $orCount=count($info['ors']);
            $allCount=$andCount+$orCount;
            $parentAndCount=$parent?count($parent['ands']):0;
            $parentorCount=$parent?count($parent['ors']):0;
            $parentAllCount=$parentAndCount+$parentAndCount;

            $showK=($parentType==='or'&&$allCount>1)
                ||($parentType==='and'&&$parentorCount>0&&$allCount>1)
                ||($orCount>0&&$parentAllCount>0&&$allCount>1);

            $str='';
            if($showK){
                if($dumpIsHtml){
                    $str.='<div class="wds-k wdsk-l">(</div>';
                }else{
                    $str.=' ( ';
                }
            }
            if($dumpIsHtml){
                $str.='<div class="wds-row-box '.($showK?'wds-show-k':'no-wds-show-k').'" data-all-count="'.$allCount.'" data-and-count="'.$andCount.'" 
                    data-or-count="'.$orCount.'" 
                    data-parent-all-count="'.$parentAllCount.'" 
                    data-parent-and-count="'.$parentAndCount.'" 
                    data-parent-or-count="'.$parentorCount.'" 
                    data-parent-type="'.$parentType.'"
                    data-guid="'.create_guid().'">';
            }

            if($info['ands']){
                if($dumpIsHtml){
                    $str.='<div class="wds-ands-box">';
                }
                foreach ($info['ands'] as $k=>$v){
                    if($k>0){
                        if($dumpIsHtml){
                            $str.='<div class="wds-row wds-and-break">AND</div>';
                        }else{
                            $str.=' AND ';
                        }
                    }
                    if(is_array($v)){
                        if($dumpIsHtml){
                            $str.='<div class="wds-row wds-and-childs">'.$getChildHtml($v,$level,$info,'and').'</div>';
                        }else{
                            $str.=$getChildHtml($v,$level,$info,'and');
                        }
                    }else{
                        if($dumpIsHtml){
                            $str.='<div class="wds-row wds-and">'.$v.'</div>';
                        }else{
                            $str.=$v;
                        }
                    }
                }
                if($dumpIsHtml){
                    $str.='</div>';
                }
            }
            if($info['ors']){
                if($info['ands']){
                    if($dumpIsHtml){
                        $str.='<div class="wds-row wds-or-break">OR</div>';
                    }else{
                        $str.=' OR ';
                    }
                }
                if($dumpIsHtml){
                    $str.='<div class="wds-ors-box">';
                }
                foreach ($info['ors'] as $k=>$v){
                    if($k>0){
                        if($dumpIsHtml){
                            $str.='<div class="wds-row wds-and-break">OR</div>';
                        }else{
                            $str.=' OR ';
                        }
                    }
                    if(is_array($v)){
                        if($dumpIsHtml){
                            $str.='<div class="wds-row wds-or-childs">'.$getChildHtml($v,$level,$info,'or').'</div>';
                        }else{
                            $str.=$getChildHtml($v,$level,$info,'or');
                        }
                    }else{
                        if($dumpIsHtml){
                            $str.='<div class="wds-row wds-or">'.$v.'</div>';
                        }else{
                            $str.=$v;
                        }
                    }
                }
                if($dumpIsHtml){
                    $str.='</div>';
                }
            }
            if($dumpIsHtml){
                $str.='</div>';
            }

            if($showK){
                if($dumpIsHtml){
                    $str.='<div class="wds-k wdsk-r">)</div>';
                }else{
                    $str.=')';
                }
            }
            return $str;
        };

        if($dumpIsHtml){
            $style='<style id="where-dump-style">.wds-row-box.wds-show-k{padding-left: 2em;}.wds-or-break,.wds-and-break{text-align: center;}.wds-or-break{color: #9254de;background-color: #f9f0ff;width: 2em;}.wds-and-break{color: #d48806;background-color: #fffbe6;width: 3em;}.wds-k{font-weight: bold;color: #237804;}</style>';

            return $style.$getChildHtml($tree);
        }
        return $getChildHtml($tree);
    }
}