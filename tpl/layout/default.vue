<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{:tpScriptVueCurdGetHtmlTitle()}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="/tpscriptvuecurd/arco/arco.min.css?2.44.2" media="all">
    <link rel="stylesheet" href="/tpscriptvuecurd/css/vue.css?v={$vueCurdDebug?time():$vueCurdVersion}" media="all">
    {if !empty($themCssPath)}
    <link rel="stylesheet" href="{$themCssPath}" media="all">
    {/if}
    <script src="/tpscriptvuecurd/vue3/vue.global.prod.js?3.2.47" charset="utf-8"></script>
    <script src="/tpscriptvuecurd/arco/arco-vue.min.js?2.44.2" charset="utf-8"></script>
    <script src="/tpscriptvuecurd/arco/arco-vue-icon.min.js?2.44.2" charset="utf-8"></script>
    <script>
        window.VUE_CURD={
            GUID: "{$guid}",
            ACTION: "{$vueCurdAction}",
            CONTROLLER: "{$vueCurdController}",
            MODULE:'{$vueCurdModule}',
            VERSION: "{$vueCurdVersion|default='1.0.0'}",
            SITE_VERSION:'{$version|default=\'\'}',
            DEBUG:'{$vueCurdDebug?1:0}'==='1',
            REQUIRES:{$jsRequires|default=false|json_encode|raw}||{},
        };
    </script>
    <script>window.vueData={$vue_data_json|raw};</script>
</head>
<body>
<div id="app-loading" class="app-loading">
    <svg viewBox="25 25 50 50">
        <circle cx="50" cy="50" r="20"></circle>
    </svg>
</div>
<div id="app" style="display: none">
    <div style="width: 100%" class="app-content-box">
        {__CONTENT__}
    </div>
    <a-modal
        v-for="bodyModal in bodyModals"
        modal-class="body-iframe-modal"
        unmount-on-close
        :width="bodyModal.width"
        :height="bodyModal.height"
        :mask-closable="false"
        :closable="bodyModal.closable"
        :esc-to-close="false"
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

<script src="/tpscriptvuecurd/require-2.3.6/require.js" charset="utf-8"></script>
<script src="/tpscriptvuecurd/require-config.js?v={$vueCurdDebug?time():$vueCurdVersion}" charset="utf-8"></script>
<script>
    let jsPath='{$jsPath|default=""}';
    if(jsPath===''){
        jsPath='{:tpScriptVueCurdPublicActionJsPathBase()}/'+window.VUE_CURD.CONTROLLER.replace(/\./g,'/')
            .replace(/(\w)([A-Z])/g,"$1_$2")
            .toLowerCase()+'.js'
    }

    let isInit=false,oldLoad=window.onload;
    window.onload=function (e){
        if(isInit){
            return;
        }
        isInit=true;
        if(oldLoad){
            oldLoad(e);
        }
        new Promise(()=>{
            require([jsPath], function (objs) {
                if(objs[VUE_CURD.ACTION]){
                    objs[VUE_CURD.ACTION]();
                }
            });
        })
    }
</script>
</body>
</html>