<style>
.field-group-fieldset>.row{
    display: flex;
    font-size: 14px;
    font-variant: tabular-nums;
    line-height: 1.5715;
    vertical-align: top;
    padding: 12px 18px;
}
.field-group-fieldset>.row:nth-child(odd){
    background-color: #fbfbff;
    border-top:1px solid #e9f3ff;
    border-bottom:1px solid #e9f3ff;
}
.field-group-fieldset>.row>.l{
    padding-right: .5em;
    width: 16.66666667%;
    /*text-align: right;*/
    color: rgba(0,0,0,.85);
    color: #000;
    font-weight: bold;
    letter-spacing: .08em;
    text-shadow: 1px 1px 1px rgba(0,0,0,0.08);
}
.field-group-fieldset>.row>.r{
    flex: 1;
    color: rgba(0,0,0,.65);
}
</style>
<div class="vuecurd-def-box">
<template v-for="(groupFieldItems,groupTitle) in groupFields">
    <fieldset class="field-group-fieldset" :class="{'show-group':haveGroup}">
        <div class="legend-box">
            <legend>{{groupTitle}}</legend>
        </div>
        <div v-for="field in groupFieldItems" class="row">
            <div class="l">
                {{field.title}}：
            </div>
            <div class="r">
                <curd-show-field :field="field" :info="info"></curd-show-field>
            </div>
        </div>
    </fieldset>
</template>
</div>