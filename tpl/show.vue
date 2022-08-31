{block name="extend"}{/block}
{block name="style"}{/block}
{block name="bodyTop"}{/block}
<div class="vuecurd-def-box vuecurd-show-def-box">
<template v-for="(groupFieldItems,groupTitle) in groupFields">
    <fieldset class="field-group-fieldset" :class="{'show-group':haveGroup}">
        <div class="legend-box">
            <legend>
                {block name="groupTitle"}
                    {{groupTitle}}
                {/block}
            </legend>
        </div>
        <div class="show-group-field-rows" :class="{'is-grid-box':!!groupGrids[groupTitle]}" :style="gridStyle(groupTitle)">
            <template v-for="field in groupFieldItems">
                <a-row class="row" v-if="!field.showUseComponent" :style="groupGrids[groupTitle]?fieldStyle(field):{}">
                    <a-col class="l" v-bind="groupGrids[groupTitle]?field.editLabelCol:{}">
                        {block name="fieldTitle"}
                        {{field.title}}：
                        {/block}
                    </a-col>
                    <a-col class="r" v-bind="groupGrids[groupTitle]?field.editWrapperCol:{}">
                        <curd-show-field :field="field" :info="info"></curd-show-field>
                    </a-col>
                </a-row>
                <component
                    v-else-if="fieldComponents['VueCurdShow'+field.type]"
                    :is="'VueCurdShow'+field.type"
                    :field="field"
                    :info="info"
                    :style="fieldStyle(field)"
                ></component>
            </template>
        </div>
    </fieldset>
</template>
</div>
{block name="bodyBottom"}{/block}
{block name="script"}{/block}