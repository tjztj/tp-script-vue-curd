<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="/tp-script-vue-curd-static.php?css/vue.css?v={$vueCurdDebug?time():$vueCurdVersion}" media="all">
</head>
<body>
<div id="app-loading">
    <svg viewBox="25 25 50 50">
        <circle cx="50" cy="50" r="20"></circle>
    </svg>
</div>
<div id="app" style="display: none">
    <a-config-provider :locale="zhCn()">
        <div class="vuecurd-def-box">
            <a-spin :spinning="loading">
                <a-form :model="form" :label-col="labelCol" :wrapper-col="wrapperCol" ref="pubForm">
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

            <div class="foot">
                <a-divider dashed style="margin-top: 0"></a-divider>
                <div class="btns">
                    <a-button type="primary" @click="onSubmit" :loading="loading"><check-outlined v-show="!loading"></check-outlined> <span>{{subBtnTitle}}</span></a-button>
                </div>
            </div>
        </div>
    </a-config-provider>
    <a-modal
        v-for="bodyModal in bodyModals"
        wrap-class-name="body-iframe-modal"
        :destroy-on-close="true"
        v-model:visible="bodyModal.visible"
        :confirm-loading="confirmLoading"
        :footer="null"
        :keyboard="false"
        :mask-closable="false"
        :width="bodyModal.width"
        :height="bodyModal.height"
        :z-index="bodyModal.zIndex"
        :after-close="bodyModal.onclose"
        @ok="handleOk"
    >
        <template #title>
            <div v-html="bodyModal.title"></div>
        </template>
        <iframe scrolling="auto" allowtransparency="true" class="" frameborder="0" :src="bodyModal.url" :onload="bodyModal.onload" :style="{height:bodyModal.height==='auto'?'auto':'calc('+bodyModal.height+' - 56px)'}"></iframe>
    </a-modal>
    <a-drawer
        v-for="bodyDrawer in bodyDrawers"
        wrap-class-name="body-iframe-drawer"
        :destroy-on-close="true"
        :mask-closable="false"
        :width="bodyDrawer.width"
        :height="bodyDrawer.height"
        :z-index="bodyDrawer.zIndex"
        :placement="bodyDrawer.placement"
        v-model:visible="bodyDrawer.visible"
        :keyboard="false"
        @close="bodyDrawer.onclose"
    >
        <template #title>
            <div v-html="bodyDrawer.title"></div>
        </template>
        <iframe scrolling="auto" allowtransparency="true" class="" frameborder="0" :src="bodyDrawer.url" :onload="bodyDrawer.onload"></iframe>
    </a-drawer>
    <div style="display: none" id="vue-curd-imgs-show-box">
        <a-image-preview-group>
            <a-image v-for="item in imgShowConfig.list" :src="item" />
        </a-image-preview-group>
    </div>
</div>

</body>
</html>