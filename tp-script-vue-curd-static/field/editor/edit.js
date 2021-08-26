define(['/tp-script-vue-curd-static.php?wangeditor/wangEditor.min.js'], function (wangEditor) {
    const styleId='editor-edit-field-style';
    const style = `
<style id="${styleId}">
[data-we-id].source-code .w-e-text-container{
    color: #93a1a1;
    background: #002b36;
}
[data-we-id].source-code .w-e-menu:not(.source-code){
opacity: .4;
cursor: not-allowed;
pointer-events: none;
}
</style>
`;

    function htmlBtn(editor){
        editor.isHTML=false;
        class AlertMenu extends wangEditor.BtnMenu {
            constructor(editor) {
                super(wangEditor.$(`<div class="w-e-menu source-code" data-title="源码"><i><svg t="1626331722208" class="icon" style="width: 1em;height: 1em;vertical-align: middle;fill: currentColor;overflow: hidden;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2787"><path d="M927.744 475.648l-178.176-178.176a51.2 51.2 0 0 0-72.192 72.192L819.2 512l-142.336 142.336a51.2 51.2 0 1 0 72.192 72.192l178.176-178.176a51.2 51.2 0 0 0 0.512-72.704zM335.872 297.472a51.2 51.2 0 0 0-72.192 0L84.992 475.648a51.2 51.2 0 0 0 0 72.192l178.176 178.176a51.2 51.2 0 0 0 72.192-72.192L194.048 512l141.824-142.336a51.2 51.2 0 0 0 0-72.192zM578.048 286.208a51.2 51.2 0 0 0-66.048 30.208l-131.584 355.328a51.2 51.2 0 1 0 95.744 35.84l131.584-355.328a51.2 51.2 0 0 0-29.696-66.048z" p-id="2788"></path></svg></i></div>`), editor)
            }
            clickHandler() {
                editor.isHTML = !editor.isHTML
                let _source;
                const textArea = document.createElement('textarea');
                if (editor.isHTML) {
                    textArea.innerText=editor.txt.html();
                    _source = textArea.innerHTML;
                    wangEditor.$('[data-we-id]').addClass('source-code')
                } else {
                    textArea.innerHTML=editor.txt.text();
                    _source = textArea.value
                    wangEditor.$('[data-we-id]').removeClass('source-code')
                }
                editor.txt.html(_source)
                this.tryChangeActive()
            }
            tryChangeActive() {
                if (editor.isHTML) this.active()
                else this.unActive()
            }
        }

        // 注册菜单
        editor.menus.extend('editHtml', AlertMenu)
    }


    return {
        props: ['field', 'value', 'validateStatus'],
        setup(props, ctx) {
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('afterend', style);
            }
            return {
                editorId: 'edit-'+window.guid(),
            }
        },
        data() {
            return {
                spinning: true,
            }
        },
        mounted() {
            const editor =new wangEditor('#'+this.editorId)
            if(this.field.zIndex){
                editor.config.zIndex = this.field.zIndex
            }
            if(this.field.height){
                editor.config.height=this.field.height;
            }
            editor.config.menus=this.field.toolbar;
            // 配置 onchange 回调函数
            editor.config.onchange =  (newHtml)=> {
                if(editor.isHTML){
                    const textArea = document.createElement('textarea');
                    textArea.innerHTML=newHtml;
                    newHtml = textArea.value;
                }
                this.$emit('update:value',newHtml);
            };
            //一次只能上传一张
            editor.config.uploadImgMaxLength=1;
            editor.config.customUploadImg =  (resultFiles, insertImgFn)=> {
                // resultFiles 是 input 中选中的文件列表
                // insertImgFn 是获取图片 url 后，插入到编辑器的方法
                let formData = new FormData();
                formData.append("file", resultFiles[0]);
                appPage.postDataAndUpload(formData, this.field.uploadUrl).then(function (res) {
                    // 上传图片，返回结果，将图片插入到编辑器中
                    insertImgFn(res.data.url)
                })
            }

            htmlBtn(editor)

            editor.create()

            const textArea = document.createElement('textarea');
            textArea.innerHTML = this.value;
            editor.txt.html(textArea.value)
            this.spinning=false;
        },
        template: `<div> <a-spin :spinning="spinning" tip="Loading..."><div :id="editorId" style="display: block"></div></a-spin></div>`,
    }
});