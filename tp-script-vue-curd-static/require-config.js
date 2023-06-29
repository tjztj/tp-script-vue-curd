window.BASE_URL = document.scripts[document.scripts.length - 1].src.substring(0, document.scripts[document.scripts.length - 1].src.lastIndexOf("/") + 1);
require.config({
    urlArgs: function(key,url){
        if(window.VUE_CURD.DEBUG){
            return '?time='+(new Date()).getTime()
        }
        //可以自定义js版本号
        if(window.VUE_CURD.SITE_VERSION!==''&&url.indexOf('tpscriptvuecurd/')===-1){
            return '?site_version='+window.VUE_CURD.SITE_VERSION;
        }
        return "?v=" + window.VUE_CURD.VERSION
    },
    baseUrl: window.BASE_URL,
    waitSeconds:0,
    map: {
        '*': {
            'css': '/tpscriptvuecurd/require-css-0.1.10/css.min.js'
        }
    },
    paths: {
        "vue": ["/tpscriptvuecurd/vue3/vue.global.prod.js"],
        "vueAdmin": ["/tpscriptvuecurd/vue-admin/vue-admin.js"],
        "axios": ["/tpscriptvuecurd/axios/axios.min.js"],
        "qs": ["/tpscriptvuecurd/qs-6.9.4/qs.min.js"],
        "g6": ["/tpscriptvuecurd/antv/g6.min.js"],
        "@wangeditor/editor": ["/tpscriptvuecurd/field/editor/wangeditor/index.min.js"],
        "@wangeditor/editor-for-vue": ["/tpscriptvuecurd/field/editor/wangeditor/index_vue.js"],
        ...window.VUE_CURD.REQUIRES,
    },
    shim:{
        '@wangeditor/editor':{
            deps: ['css!/tpscriptvuecurd/field/editor/wangeditor/style']
        },
    }
});
