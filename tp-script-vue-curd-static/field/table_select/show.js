define([],function(){
    return {
        props:['info','field'],
        setup:function (props,ctx){
            const loading=Vue.ref(true);
            const text=Vue.ref('');
            window.tableSelectIndexTextObjs=Vue.ref({});
            window.tableSelectIndexValuesNeedGets=window.tableSelectIndexValuesNeedGets||{};

            const pushTexts=function (id,val){
                window.tableSelectIndexTextObjs.value[props.field.url]=window.tableSelectIndexTextObjs.value[props.field.url]||{};
                window.tableSelectIndexTextObjs.value[props.field.url][id]=val;
            };
            const getText=function (id){
                window.tableSelectIndexTextObjs.value[props.field.url]=window.tableSelectIndexTextObjs.value[props.field.url]||{};
                if(typeof window.tableSelectIndexTextObjs.value[props.field.url][id]==='undefined'){
                    return null;
                }
                return window.tableSelectIndexTextObjs.value[props.field.url][id];
            }


            const setTitles=function (vals){
                if(!vals||vals.length===0){
                    return;
                }
                window.tableSelectIndexValuesNeedGets[props.field.url]=window.tableSelectIndexValuesNeedGets[props.field.url]||[];
                window.tableSelectIndexValuesNeedGets[props.field.url].push(...vals);
                setTimeout(()=>{
                    if(window.tableSelectIndexValuesNeedGets[props.field.url].length===0){
                        return;
                    }
                    const idArr=window.tableSelectIndexValuesNeedGets[props.field.url];
                    window.tableSelectIndexValuesNeedGets[props.field.url]=[];

                    window.vueDefMethods.$post.call(window.appPage||ctx,props.field.url,{ids:idArr}).then(res=>{
                        if(typeof res.data==='undefined'){
                            for(let id of idArr){
                                pushTexts(id,'');
                            }
                            return;
                        }
                        let showField=Object.keys(props.field.fields)[0];
                        let texts=[];
                        if(Array.isArray(res.data)&&typeof res.data[0]==='object'){
                            res.data.forEach(v=>{
                                if(!idArr.includes(v.id.toString())){
                                    return;
                                }
                                texts.push(v[showField])
                            })
                        }else if(typeof res.data.current_page!=='undefined'){
                            res.data.data.forEach(v=>{
                                pushTexts(v.id,v[showField]);
                            })
                        }else{
                            for(let k in res.data){
                                pushTexts(k,typeof res.data[k]!=='string'&&typeof res.data[k]!=='number'?'':res.data[k]);
                            }
                        }
                    });
                },40);
            }

            Vue.watchEffect(function (){
                let vals=[],newValue=props.info[props.field.name];
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
                let newVals=[],texts=[];
                for (let id of vals){
                    let t=getText(id);
                    if(t===null){
                        newVals.push(id)
                    }else{
                        texts.push(t)
                    }
                }
                if(newVals.length===0){
                    loading.value=false;
                    text.value=texts.join('，');
                    return;
                }
                setTitles(newVals)
            },{ flush: 'post'})


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