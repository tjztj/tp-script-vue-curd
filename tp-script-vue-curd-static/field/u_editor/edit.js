define(['/tpscriptvuecurd/field/u_editor/ueditor-plus-2.9.0/config.js','/tpscriptvuecurd/field/u_editor/ueditor-plus-2.9.0/vue-ueditor-wrap.js'], function (config) {
    const styleId='u-editor-field-edit-stype';
    const style = `
<style id="${styleId}">

</style>
`;



    return {
        components:{
            'vue-ueditor-wrap':Vue.defineAsyncComponent(()=>{
                return new Promise(function (resolve){
                    require(['vue-ueditor-wrap'],function (VueUeditorWrap){
                        window.app.use(VueUeditorWrap);
                        resolve(window.app.component('VueUeditorWrap'))
                    })
                })
            })
        },
        props: ['field', 'value', 'validateStatus','form'],
        setup(props){
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }

            return {
                id:'ueditor-'+window.guid(),
                editorConfig:{
                    ...config,
                    zIndex:props.field.zIndex,
                    serverUrl: props.field.uploadUrl,
                    // 配置UEditorPlus的惊天资源
                    UEDITOR_HOME_URL: '/tpscriptvuecurd/field/u_editor/ueditor-plus-2.9.0/'
                },
                content: Vue.ref(props.value||''),
            }
        },
        computed: {
        },
        watch:{
            content(val){
                this.$emit('update:value',val)
            },
        },
        methods: {
            beforeInit(){
                window.UEDITOR_CONFIG.toolbars=this.field.toolbar;
            },
        },
        template: `<div class="field-box">
                    <div class="l" style="padding-bottom: 2px">
                          <vue-ueditor-wrap v-model="content"
                          @before-init="beforeInit"
                          :editorId="id"
                          :config="editorConfig"
                          :style="{height:field.height+'px'}"
                          style="width:100%"/>
                    </div>
                    <div class="r"></div>
                </div>`,
    }
});