define(['/tpscriptvuecurd/field/iframe/iframe.js'],function(iframeBox){
    return {
        components:{
            iframeBox
        },
        props: ['field', 'value', 'validateStatus', 'form'],
        template:`<iframeBox :field="field" :info="form"></iframeBox>`,
    }
});