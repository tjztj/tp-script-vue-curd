window.BASE_URL = document.scripts[document.scripts.length - 1].src.substring(0, document.scripts[document.scripts.length - 1].src.lastIndexOf("/") + 1);
require.config({
    urlArgs: function(key,url){
        if(window.VUE_CURD.DEBUG){
            return '?time='+(new Date()).getTime()
        }
        //可以自定义js版本号
        if(window.VUE_CURD.SITE_VERSION!==''&&url.indexOf('tp-script-vue-curd-static.php')===-1){
            return '?site_version='+window.VUE_CURD.SITE_VERSION;
        }
        return "?v=" + window.VUE_CURD.VERSION
    },
    baseUrl: window.BASE_URL,
    waitSeconds:0,
    map: {
        '*': {
            'css': 'plugs/require-css-0.1.10/css.min'
        }
    },
    paths: {
        "vue": ["tp-script-vue-curd-static.php?vue3/vue.global.prod.js"],
        "vueAdmin": ["tp-script-vue-curd-static.php?vue-admin/vue-admin.js"],
        "axios": ["tp-script-vue-curd-static.php?axios/axios.min.js"],
        "qs": ["tp-script-vue-curd-static.php?qs-6.9.4/qs.min.js"],
        "g6": ["tp-script-vue-curd-static.php?antv/g6.min.js"],
        ...window.VUE_CURD.REQUIRES,
    },
});
