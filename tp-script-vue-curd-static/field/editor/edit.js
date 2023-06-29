define(['@wangeditor/editor', '@wangeditor/editor-for-vue'], function (wangEditor, wangEditorVue) {
    const styleId = 'editor-edit-field-style';
    const style = `
<style id="${styleId}">
.wedit-field-box{
    border: 1px solid #e5e6eb;border-radius: 1px
}
.wedit-field-box:hover{
  border-color: #c9cdd4;
}
.wedit-field-box.on-focus,.wedit-field-box.on-focus:hover{
    border-color: #a9aeb8;
}
.wedit-field-toolbar{
border-bottom: 1px solid #e5e6eb;
}
.wedit-field-box.on-focus .wedit-field-toolbar,.wedit-field-box:hover .wedit-field-toolbar{
border-color: #c9cdd4;
}
</style>
`;



    return {
        components: {
            Editor: wangEditorVue.Editor,
            Toolbar: wangEditorVue.Toolbar,
        },
        props: ['field', 'value', 'validateStatus'],
        setup(props) {
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }
            // 编辑器实例，必须用 shallowRef
            const editorRef = Vue.shallowRef()

            // 内容 HTML
            let val = '';
            if (props.value) {
                const textArea = document.createElement('textarea');
                textArea.innerHTML = props.value;
                val = textArea.value;
            }
            const valueHtml = Vue.ref(val)


            const toolbarConfig = {
                toolbarKeys: props.field.toolbar
            }
            const editorConfig = {
                placeholder: props.field.placeholder || '请输入内容...',
                MENU_CONF:{
                    uploadImage:{
                        server: props.field.uploadUrl,
                        fieldName:'file',
                        customInsert(res, insertFn) {
                            if(!res.code||parseInt(res.code)!==1){
                                ArcoVue.Message.error(res.msg||'上传失败');
                            }
                            let arr=res.data.url.split('/');
                            insertFn(res.data.url, arr[arr.length-1], res.data.url)
                        }
                    },
                    uploadVideo:{
                        server: props.field.uploadUrl,
                        fieldName:'file',
                        customInsert(res, insertFn) {
                            if(!res.code||parseInt(res.code)!==1){
                                ArcoVue.Message.error(res.msg||'上传失败');
                            }
                            insertFn(res.data.url)
                        }
                    },
                }
            }

            // 组件销毁时，也及时销毁编辑器
            Vue.onBeforeUnmount(() => {
                const editor = editorRef.value
                if (editor == null) return
                editor.destroy()
            })

            const spinning = Vue.ref(true);
            const handleCreated = (editor) => {
                editorRef.value = editor // 记录 editor 实例，重要！
                spinning.value = false;
            }

            return {
                editorRef,
                valueHtml,
                mode: 'default', // 或 'simple'
                toolbarConfig,
                editorConfig,
                handleCreated,
                spinning,
                hover:Vue.ref(false),
            };
        },
        mounted() {
        },
        methods:{
            handleChange(editor){
                this.$emit('update:value',editor.getHtml());
            },
            handleFocus(){
                this.hover=true;
            },
            handleBlur(){
                this.hover=false;
            },
        },
        template: `<div> <a-spin :loading="spinning" tip="Loading..." style="display: block">
                         <div class="wedit-field-box" :class="{'on-focus':hover}" :style="{zIndex:field.zIndex||''}">
                             <Toolbar
                                class="wedit-field-toolbar"
                                :editor="editorRef"
                                :defaultConfig="toolbarConfig"
                                :mode="mode"
                            />
                            <Editor
                                :style="{height:field.height+'px','overflow-y':'hidden'}"
                                v-model="valueHtml"
                                :defaultConfig="editorConfig"
                                :mode="mode"
                                @onCreated="handleCreated"
                                @onChange="handleChange"
                                @onFocus="handleFocus"
                                @onBlur="handleBlur"
                            />
                        </div>
                    </a-spin></div>`,
    }
});