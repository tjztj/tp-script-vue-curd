<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        .app-loading {
            position: fixed;
            z-index: 100000000;
            top: 0;
            bottom: 0;
            left: 0;
            right: 0;
            margin: auto;
            width: 3.3em;
            height: 3.3em;
        }

        .app-loading svg {
            width: 100%;
            transform-origin: center;
            animation: rotate 2s linear infinite;
        }

        .app-loading circle {
            fill: none;
            stroke: #2196f3;
            stroke-width: 2;
            stroke-dasharray: 1, 200;
            stroke-dashoffset: 0;
            stroke-linecap: round;
            animation: dash 1.5s ease-in-out infinite;
        }

        @keyframes rotate {
            100% {
                transform: rotate(360deg);
            }
        }

        @keyframes dash {
            0% {
                stroke-dasharray: 1, 200;
                stroke-dashoffset: 0;
            }
            50% {
                stroke-dasharray: 90, 200;
                stroke-dashoffset: -35px;
            }
            100% {
                stroke-dashoffset: -125px;
            }
        }
    </style>
</head>
<body style="background-color: #fff">
<div id="app-loading" class="app-loading">
    <svg viewBox="25 25 50 50">
        <circle cx="50" cy="50" r="20"></circle>
    </svg>
</div>
<div id="app" style="display: none">
    <div class="vuecurd-def-box">
        <a-spin :loading="loading" style="display: block">
            <a-form :model="form" :label-col-props="labelCol" :wrapper-col-props="wrapperCol" ref="pubForm">
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
            </a-form>
        </a-spin>

        <div class="foot">
            <a-divider style="margin-top: 0;border-bottom-style: dotted"></a-divider>
            <div class="btns">
                <a-button type="primary" @click="onSubmit" :loading="loading"><template #icon><icon-check></icon-check></template><span>{{subBtnTitle}}</span></a-button>
            </div>
        </div>
    </div>
    <a-modal
        v-for="bodyModal in bodyModals"
        modal-class="body-iframe-modal"
        unmount-on-close
        :width="bodyModal.width"
        :height="bodyModal.height"
        :mask-closable="false"
        :esc-to-close="false"
        :closable="bodyModal.closable"
        :footer="false"
        v-model:visible="bodyModal.visible"
        :z-index="bodyModal.zIndex"
        @close="bodyModal.onclose"
        @before-close="bodyModal.onBeforeClose"
    >
        <template #title>
            <div v-html="bodyModal.title"></div>
        </template>
        <iframe scrolling="auto" allowtransparency="true" class="" frameborder="0" :src="bodyModal.url" :onload="bodyModal.onload" :style="{height:bodyModal.height==='auto'?'auto':'calc('+bodyModal.height+' - 96px)'}"></iframe>
    </a-modal>
    <a-drawer
        v-for="bodyDrawer in bodyDrawers"
        v-model:visible="bodyDrawer.visible"
        class="body-iframe-drawer"
        :width="bodyDrawer.width"
        :height="bodyDrawer.height"
        :visible="bodyDrawer.visible"
        :placement="bodyDrawer.placement"
        :esc-to-close="false"
        :mask-closable="false"
        :closable="bodyDrawer.closable"
        :footer="false"
        hide-cancel
        unmount-on-close
        @close="bodyDrawer.onclose"
        @before-close="bodyDrawer.onBeforeClose"
    >
        <template #title>
            <div v-html="bodyDrawer.title"></div>
        </template>
        <iframe scrolling="auto" allowtransparency="true" class="" frameborder="0" :src="bodyDrawer.url" :onload="bodyDrawer.onload"></iframe>
    </a-drawer>
    <div v-if="imgShowConfig.list&&imgShowConfig.list.length>0" id="vue-curd-imgs-show-box">
        <a-image-preview-group>
            <a-image v-for="item in imgShowConfig.list" :src="item" />
        </a-image-preview-group>
    </div>
</div>

</body>
</html>