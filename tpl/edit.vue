{block name="extend"}{/block}
<style>

</style>
{block name="style"}{/block}
<div class="vuecurd-def-box">
<a-spin :spinning="loading">
    <a-form :model="form" :label-col="labelCol" :wrapper-col="wrapperCol" ref="pubForm">
      {block name="formBefore"}{/block}
        <template v-for="(groupFieldItems,groupTitle) in groupFields">
            <template v-if="showGroup">
                <fieldset class="field-group-fieldset show-group" v-show="checkShowGroup(groupFieldItems)">
                    <div class="legend-box">
                        <legend>{{groupTitle}}</legend>
                    </div>
                    <field-group-item
                        :list-field-label-col="labelCol"
                        :list-field-wrapper-col="wrapperCol"
                        :group-field-items="groupFieldItems"
                        :info="info"
                        :grid="groupGrids[groupTitle]"
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
                    :grid="groupGrids[groupTitle]"
                    v-model:field-hide-list="fieldHideList"
                    v-model:form="form"
                    @submit="onSubmit($event)"
                    :ref="'fieldGroup'+groupTitle"></field-group-item>
            </template>
        </template>
      {block name="formAfter"}{/block}
    </a-form>
</a-spin>

<div class="foot">
    <a-divider dashed style="margin-top: 0"></a-divider>
    {block name="footBefore"}{/block}
    <div class="btns">
        <a-button type="primary" @click="onSubmit" :loading="loading"><check-outlined v-show="!loading"></check-outlined> <span>{{form.id?'{$isStepNext?"提交":"确定修改"}':'执行添加'}}</span></a-button>
    </div>
    {block name="footAfter"}{/block}
</div>

</div>
{block name="script"}{/block}