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
        <template v-for="field in groupFieldItems">
            <div class="row" v-if="!field.showUseComponent">
                <div class="l">
                    {block name="fieldTitle"}
                        {{field.title}}：
                    {/block}
                </div>
                <div class="r">
                    <curd-show-field :field="field" :info="info"></curd-show-field>
                </div>
            </div>
            <component
                v-else-if="fieldComponents['VueCurdShow'+field.type]"
                :is="'VueCurdShow'+field.type"
                :field="field"
                :info="info"
            ></component>
        </template>
    </fieldset>
</template>
</div>
{block name="bodyBottom"}{/block}
{block name="script"}{/block}