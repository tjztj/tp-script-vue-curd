define([],function(){
    const styleId='url_page-field-style';
    const style = `
<style id="${styleId}">
</style>
`;
    const iframeStyle = `
<style>
body{
padding: 0;
}
.vuecurd-def-box{
padding-bottom: 0;
}
.ant-form-item{
margin-bottom: 0;
}
</style>
`;
    return {
        props: ['info','field'],
        setup(props, ctx) {
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('afterend', style);
            }


            return {
                iframeId:window.guid(),
                height:Vue.ref(200),
                loading:Vue.ref(true),
            }
        },
        computed:{
            url(){
               return this.field.url||''
            }
        },
        methods:{
            iframeLoad(){
                const childWindow=document.getElementById(this.iframeId).contentWindow;
                childWindow.document.querySelector('head').insertAdjacentHTML('afterend', iframeStyle);

                const html = childWindow.document.documentElement,body = childWindow.document.body;

                const setH=()=> {
                    const h=Math.max( body.scrollHeight, body.offsetHeight);
                    this.height=h<200?200:h;
                    setTimeout(()=>{
                        let newH=Math.max(body.scrollHeight, body.offsetHeight,html.clientHeight, html.scrollHeight, html.offsetHeight);
                        if(this.height!==newH){
                            this.height=newH;
                        }
                    })
                }
                const MutationObserver = childWindow.MutationObserver || childWindow.webkitMutationObserver || childWindow.MozMutationObserver;
                body.addEventListener('animationend', setH)
                body.addEventListener('transitionend', setH)
                const observer= new MutationObserver((mutations) => {
                    setH()
                });
                observer.observe(body, {
                    childList: true,
                    subtree: true,
                    characterData: true,
                    attributes: true
                })
                childWindow.onresize = setH;
                setH();
                this.loading=false;
            }
        },
        template:`
        <div>
          <a-divider v-if="field.topHrText!=''">{{field.topHrText}}</a-divider>
        <a-spin tip="加载中..." :loading="loading" style="display: block">
        <iframe v-if="url" :src="url" :onload="iframeLoad" width="100%" :height="height+'px'" frameborder="0" :id="iframeId" class="url_page-iframe"></iframe>
        </a-spin>
        <a-divider v-if="field.bottomHrText!=''">{{field.bottomHrText}}</a-divider>
</div>`,
    }
});