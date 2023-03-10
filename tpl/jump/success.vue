<div>
<div class="arco-result result">
    <div class="arco-result-icon arco-result-icon-success">
        <div class="arco-result-icon-tip">
            <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor"
                 class="arco-icon arco-icon-check" stroke-width="4" stroke-linecap="butt" stroke-linejoin="miter">
                <path d="M41.678 11.05 19.05 33.678 6.322 20.95"></path>
            </svg>
        </div>
    </div>
    <div class="arco-result-title">成功</div>
    <div class="arco-result-subtitle">{{ msg }}</div>
    <div class="arco-result-extra">
        <div class="arco-space arco-space-horizontal arco-space-align-center operation-wrap">
            <div class="arco-space-item">
                <button v-show="showBack"
                    class="arco-btn arco-btn-primary arco-btn-shape-square arco-btn-size-medium arco-btn-status-normal"
                    type="button" @click="back">返回
                </button>
            </div>
            <div class="arco-space-item" style="margin-left: 16px;" @click="close" v-if="isIframe">
                <button
                    class="arco-btn arco-btn-secondary arco-btn-shape-square arco-btn-size-medium arco-btn-status-normal"
                    type="button">关闭当前页面
                </button>
            </div>
        </div>
    </div><!--v-if--></div>
</div>
<script>
window.app = Vue.createApp({
    data() {
        return {
            msg: vueData.msg,
            isIframe: self.frameElement && self.frameElement.tagName == "IFRAME",
            showBack: false,
        }
    },
    created(){
        window.iframeLoad=()=>{
            this.showBack=window.getHistory&&window.getHistory().index>0;
            if(!this.showBack&&!this.isIframe){
                this.showBack=true;
            }
        };
        window.onload=()=>{
            setTimeout(()=>{
                if(!this.showBack&&!this.isIframe){
                    this.showBack=true;
                }
            },200)
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