window.BASE_URL = document.scripts[document.scripts.length - 1].src.substring(0, document.scripts[document.scripts.length - 1].src.lastIndexOf("/") + 1);
require.config({
    urlArgs: "?v=" + window.VUE_CURD.VERSION,
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
        "axios": ["tp-script-vue-curd-static.php?axios-0.19.2/axios.min.js"],
        "qs": ["tp-script-vue-curd-static.php?qs-6.9.4/qs.min.js"],
        'antDesignVue': ["tp-script-vue-curd-static.php?ant-design-vue/antd.min.js"],
        "moment": ["tp-script-vue-curd-static.php?moment/moment.min.js"],
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
