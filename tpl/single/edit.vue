<style>
.vuecurd-def-box .ext-span{
    display: inline-block;
    margin-top: 5px;
}
</style>
{if $title}<h2 style="padding: 24px;margin: 0;font-size: 18px;font-weight: bold">{$title}</h2>{else/}<div style="height: 30px">&nbsp;</div>{/if}
<div class="vuecurd-def-box" style="padding: 6px 0 36px 0">

<a-spin :spinning="loading">
    <a-form :model="form" :label-col="labelCol" :wrapper-col="wrapperCol" ref="pubForm" style="max-width: 960px">
        <template v-for="(groupFieldItems,groupTitle) in groupFields">
            <template v-if="showGroup">
                <fieldset class="field-group-fieldset show-group" v-show="checkShowGroup(groupFieldItems)">
                    <div class="legend-box">
                        <legend>{{ groupTitle }}</legend>
                    </div>
                    <field-group-item
                        :list-field-label-col="labelCol"
                        :list-field-wrapper-col="wrapperCol"
                        :group-field-items="groupFieldItems"
                        :info="info"
                        v-model:field-hide-list="fieldHideList"
                        v-model:form="form"
                        @submit="onSubmit($event)"
                        :ref="'fieldGroup'+groupTitle"></field-group-item>
                </fieldset>
            </template>
            <template v-else>
                <field-group-item
                    :list-field-label-col="labelCol"
                    :list-field-wrapper-col="wrapperCol"
                    :group-field-items="groupFieldItems"
                    :info="info"
                    v-model:field-hide-list="fieldHideList"
                    v-model:form="form"
                    @submit="onSubmit($event)"
                    :ref="'fieldGroup'+groupTitle"></field-group-item>
            </template>
        </template>
    </a-form>
</a-spin>
<a-divider dashed style="margin-top: 0"></a-divider>
<div style="width: 100%;max-width: 960px">
    <div class="btns" style="padding-left: 33.33333333%;">
        <a-button type="primary" @click="onSubmit" :loading="loading">
            <check-outlined v-show="!loading"></check-outlined>
            <span>{$subBtnTitle}</span></a-button>

        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
        {if $refreshBtnTitle}<a-button  @click="reload" :loading="loading">
            <reload-outlined v-show="!loading"></reload-outlined>
            <span>{$refreshBtnTitle}</span></a-button>{/if}
    </div>
</div>


</div>

{if $bgColor}
<style>body{background-color: {$bgColor};}</style>
{/if}