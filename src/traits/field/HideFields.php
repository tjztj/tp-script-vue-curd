<?php


namespace tpScriptVueCurd\traits\field;



trait HideFields
{

    protected bool $defHideAboutFields=true;//默认隐藏相关字段
    protected bool $reversalHideFields=false;//反转隐藏与显示的字段，我一般用在 select 多选与，checkbox。它不会对$defHideAboutFields有效果

    /**
     * 默认隐藏相关字段
     * @param bool|null $defHideAboutFields
     * @return bool|$this
     */
    public function defHideAboutFields(bool $defHideAboutFields=null){
        return $this->doAttr('defHideAboutFields',$defHideAboutFields);
    }

    /**
     * 反转隐藏与显示的字段，我一般用在 select 多选与，checkbox。它不会对$defHideAboutFields有效果
     * @param bool|null $reversalHideFields
     * @return bool|$this
     */
    public function reversalHideFields(bool $reversalHideFields=null){
        return $this->doAttr('reversalHideFields',$reversalHideFields);
    }
}