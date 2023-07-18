define(['/tpscriptvuecurd/field/iframe/iframe.js'],function(iframeBox){
    return {
        components:{
            iframeBox
        },
        props: ['info','field'],
        template:`<iframeBox :field="field" :info="info"></iframeBox>`,
    }
});