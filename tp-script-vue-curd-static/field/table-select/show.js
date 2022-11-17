define([],function(){
    return {
        props:['info','field'],
        setup:function (props,ctx){
            const loading=Vue.ref(true);
            const text=Vue.ref('');
            const textObjs={};
            Vue.watch( () => props.info[props.field.name], (newValue, oldValue) => {
                let vals=[];
                switch (typeof newValue){
                    case 'undefined':
                        break;
                    case 'string':
                        vals=newValue.toString().split(',');
                        break;
                    case 'number':
                        vals=[newValue.toString()];
                        break;
                    case 'object':
                        vals=newValue.map(v=>v.toString());
                        break;
                }
                if(vals.length===0){
                    loading.value=false;
                    text.value='';
                    return;
                }
                if(typeof textObjs[newValue]!=='undefined'){
                    loading.value=false;
                    text.value=textObjs[newValue];
                    return;
                }


                window.vueDefMethods.$post.call(window.appPage||ctx,props.field.url,{ids:vals}).then(res=>{
                    if(typeof res.data==='undefined'){
                        loading.value=false;
                        text.value='';
                        textObjs[newValue]='';
                        return;
                    }
                    let showField=Object.keys(props.field.fields)[0];
                    let texts=[];
                    if(Array.isArray(res.data)){
                        res.data.forEach(v=>{
                            if(!vals.includes(v.id.toString())){
                                return;
                            }
                            texts.push(v[showField])
                        })
                    }else if(typeof res.data.current_page!=='undefined'){
                        res.data.data.forEach(v=>{
                            if(!vals.includes(v.id.toString())){
                                return;
                            }
                            texts.push(v[showField])
                        })
                    }else{
                        for(let k in res.data){
                            let v=res.data[k];
                            if(typeof v!=='string'&&typeof v!=='number'){
                                loading.value=false;
                                text.value='';
                                textObjs[newValue]='';
                                return;
                            }
                            if(!vals.includes(k.toString())){
                                return;
                            }
                            texts.push(v)
                        }
                    }

                    loading.value=false;
                    text.value=texts.join('，');
                    textObjs[newValue]=text.value;
                });
            },{immediate:true,deep: true});

            return {
                loading,
                text,
                indicator: Vue.h("span", {
                    role: "img",
                    "aria-label": "loading",
                    class: "anticon anticon-loading"
                }, [
                    Vue.h("svg", {
                        focusable: "false",
                        class: "anticon-spin",
                        "data-icon": "loading",
                        width: "1em",
                        height: "1em",
                        fill: "currentColor",
                        "aria-hidden": "true",
                        viewBox: "0 0 1024 1024"
                    }, [
                        Vue.h("path", { d: "M988 548c-19.9 0-36-16.1-36-36 0-59.4-11.6-117-34.6-171.3a440.45 440.45 0 00-94.3-139.9 437.71 437.71 0 00-139.9-94.3C629 83.6 571.4 72 512 72c-19.9 0-36-16.1-36-36s16.1-36 36-36c69.1 0 136.2 13.5 199.3 40.3C772.3 66 827 103 874 150c47 47 83.9 101.8 109.7 162.7 26.7 63.1 40.2 130.2 40.2 199.3.1 19.9-16 36-35.9 36z" })
                    ])
                ]),
            };
        },
        methods:{

        },
        template:`<div>
<a-spin v-if="loading" :indicator="indicator"></a-spin><span v-else>{{text}}</span>
</div>`,
    }
});