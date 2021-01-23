window.BASE_URL = document.scripts[document.scripts.length - 1].src.substring(0, document.scripts[document.scripts.length - 1].src.lastIndexOf("/") + 1);
require.config({
    urlArgs: "v=" + window.VUE_CURD.VERSION,
    baseUrl: window.BASE_URL,
    waitSeconds:0,
    map: {
        '*': {
            'css': 'plugs/require-css-0.1.10/css.min'
        }
    },
    paths: {
        "vue": ["tp-script-vue-curd-static.php?vue3/vue.global.prod"],
        "vueAdmin": ["tp-script-vue-curd-static.php?vue-admin/vue-admin"],
        "axios": ["tp-script-vue-curd-static.php?axios-0.19.2/axios.min"],
        "qs": ["tp-script-vue-curd-static.php?qs-6.9.4/qs.min"],
        'antDesignVue': ["tp-script-vue-curd-static.php?ant-design-vue/antd.min"],
        "moment": ["tp-script-vue-curd-static.php?moment/moment.min"],
    },
    //css 依赖
    shim:{
        'common':{
            deps: ['css!tp-script-vue-curd-static.php?common/common']
        },
        'antDesignVue':{
            deps: ['css!tp-script-vue-curd-static.php?ant-design-vue/antd.min']
        },
    }
});
