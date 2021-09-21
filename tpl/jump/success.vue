<div>
<div class="ant-result ant-result-success" style="margin-top: 48px; margin-bottom: 16px;">
    <div class="ant-result-icon"><span role="img" aria-label="check-circle" class="anticon anticon-check-circle"><svg
        viewBox="64 64 896 896" focusable="false" data-icon="check-circle" width="1em" height="1em" fill="currentColor"
        aria-hidden="true"><path
        d="M512 64C264.6 64 64 264.6 64 512s200.6 448 448 448 448-200.6 448-448S759.4 64 512 64zm193.5 301.7l-210.6 292a31.8 31.8 0 01-51.7 0L318.5 484.9c-3.8-5.3 0-12.7 6.5-12.7h46.9c10.2 0 19.9 4.9 25.9 13.3l71.2 98.8 157.2-218c6-8.3 15.6-13.3 25.9-13.3H699c6.5 0 10.3 7.4 6.5 12.7z"></path></svg></span>
    </div>
    <div class="ant-result-title">成功</div>
    <div class="ant-result-subtitle">{{ msg }}</div>
    <div class="ant-result-extra" style="margin-top: 48px">
        <button type="button" class="ant-btn ant-btn-primary" @click="back">
            <span role="img" aria-label="left" class="anticon anticon-left"><svg focusable="false" class=""
                                                                                 data-icon="left" width="1em"
                                                                                 height="1em" fill="currentColor"
                                                                                 aria-hidden="true"
                                                                                 viewBox="64 64 896 896"><path
                d="M724 218.3V141c0-6.7-7.7-10.4-12.9-6.3L260.3 486.8a31.86 31.86 0 000 50.3l450.8 352.1c5.3 4.1 12.9.4 12.9-6.3v-77.3c0-4.9-2.3-9.6-6.1-12.6l-360-281 360-281.1c3.8-3 6.1-7.7 6.1-12.6z"></path></svg></span>
            <span>返回</span></button>
        <button v-if="isIframe" type="button" class="ant-btn ant-btn-primary" @click="close" style="margin-left: 24px">
            <span role="img" aria-label="close" class="anticon anticon-close"><svg focusable="false" class=""
                                                                                   data-icon="close" width="1em"
                                                                                   height="1em" fill="currentColor"
                                                                                   aria-hidden="true"
                                                                                   viewBox="64 64 896 896"><path
                d="M563.8 512l262.5-312.9c4.4-5.2.7-13.1-6.1-13.1h-79.8c-4.7 0-9.2 2.1-12.3 5.7L511.6 449.8 295.1 191.7c-3-3.6-7.5-5.7-12.3-5.7H203c-6.8 0-10.5 7.9-6.1 13.1L459.4 512 196.9 824.9A7.95 7.95 0 00203 838h79.8c4.7 0 9.2-2.1 12.3-5.7l216.5-258.1 216.5 258.1c3 3.6 7.5 5.7 12.3 5.7h79.8c6.8 0 10.5-7.9 6.1-13.1L563.8 512z"></path></svg></span>
            <span>关闭当前页面</span></button>
    </div>

</div>
</div>
<script>
window.app = Vue.createApp({
    data() {
        return {
            msg: vueData.msg,
            isIframe: self.frameElement && self.frameElement.tagName == "IFRAME",
        }
    },
    mounted() {
        if (document.getElementById('app')) document.getElementById('app').style.display = 'block'
        if (document.getElementById('app-loading')) document.getElementById('app-loading').style.display = 'none'
    },
    methods: {
        back() {
            window.history.back()
        },
        close() {
            document.body.dispatchEvent(new Event('closeIframe'));
        }
    }
}).mount('#app')
</script>