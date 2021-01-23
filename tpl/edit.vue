<style>

</style>
<div class="vuecurd-def-box">
<a-spin :spinning="loading">
<a-form :model="form" :label-col="labelCol" :wrapper-col="wrapperCol" ref="pubForm">
    <template v-for="(groupFieldItems,groupTitle) in groupFields">
        <template v-if="haveGroup">
            <fieldset class="field-group-fieldset show-group" v-show="checkShowGroup(groupFieldItems)">
                <div class="legend-box">
                    <legend>{{groupTitle}}</legend>
                </div>
                <field-group-item
                    :list-field-label-col="labelCol"
                    :list-field-wrapper-col="wrapperCol"
                    :group-field-items="groupFieldItems"
                    v-model:form="form"
                    :ref="'fieldGroup'+groupTitle"></field-group-item>
            </fieldset>
        </template>
        <template v-else>
            <field-group-item
                :list-field-label-col="labelCol"
                :list-field-wrapper-col="wrapperCol"
                :group-field-items="groupFieldItems"
                v-model:form="form"
                :ref="'fieldGroup'+groupTitle"></field-group-item>
        </template>
    </template>

</a-form>
</a-spin>
<div class="foot">
    <a-divider dashed style="margin-top: 0"></a-divider>
    <div class="btns">
        <a-button type="primary" @click="onSubmit" :loading="loading"><check-outlined v-show="!loading"></check-outlined> <span>{{form.id?'确定修改':'执行添加'}}</span></a-button>
    </div>
</div>
</div>