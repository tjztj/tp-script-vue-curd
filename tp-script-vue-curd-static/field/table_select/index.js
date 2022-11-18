define([],function(){
    return {
        props:['record','field','list'],
        setup(props,ctx){
            window.tableSelectIndexValues=window.tableSelectIndexValues||Vue.ref({});
            const text=Vue.ref('');
            const setTextByVal=function (){
                window.tableSelectIndexValues.value[props.field.url]=window.tableSelectIndexValues.value[props.field.url]||{};
                let arr=[];
                let showField=Object.keys(props.field.fields)[0];
                props.record.record[props.field.name].toString().split(',').forEach(v=>{
                    if(typeof window.tableSelectIndexValues.value[props.field.url][v]==='undefined'
                        ||typeof window.tableSelectIndexValues.value[props.field.url][v][showField]==='undefined'
                        ||window.tableSelectIndexValues.value[props.field.url][v][showField]===''){
                        return;
                    }
                    arr.push(window.tableSelectIndexValues.value[props.field.url][v][showField])
                })
                text.value=arr.join('，');
            };
            Vue.watch(()=>window.tableSelectIndexValues.value[props.field.url],(values)=>{
                setTextByVal();
            },{immediate:true,deep: true});
            const tableGuid=Vue.inject('table-guid')||'';
            window.tableSelectIndexValuesLastChangeTime=window.tableSelectIndexValuesLastChangeTime||{};
            window.tableSelectIndexValuesLastChangeTime[tableGuid]=window.tableSelectIndexValuesLastChangeTime[tableGuid]||null;
            Vue.watch(()=>props.list,(list)=>{
                if(window.tableSelectIndexValuesLastChangeTime[tableGuid]&&(new Date).getTime()-window.tableSelectIndexValuesLastChangeTime[tableGuid]>3500){
                    //防止更改选项里面的值后缓存
                    window.tableSelectIndexValues.value[props.field.url]={};
                }
                window.tableSelectIndexValuesLastChangeTime[tableGuid]=(new Date).getTime();


                window.tableSelectIndexValues.value[props.field.url]=window.tableSelectIndexValues.value[props.field.url]||{};
                let vals={};
                list.forEach(v=>{
                    v[props.field.name].toString().trim().split(',').forEach(vv=>{
                        vals[vv]=vals[vv]||{};
                    })
                })
                let valArr=Object.keys(vals);
                if(valArr.length===0){
                    text.value='';
                }
                let showField=Object.keys(props.field.fields)[0];
                let needGet=[];
                for(let k in vals){
                    if(typeof window.tableSelectIndexValues.value[props.field.url][k]==='undefined'){
                        window.tableSelectIndexValues.value[props.field.url][k]={};
                        needGet.push(k);
                    }
                }
                if(!needGet.length){
                    setTextByVal();
                    return;
                }
                window.vueDefMethods.$post.call(window.appPage||ctx,props.field.url,{ids:needGet}).then(res=>{
                    if(typeof res.data==='undefined'){
                        needGet.forEach(v=>{window.tableSelectIndexValues.value[props.field.url][v]={id:v,[showField]:''}})
                        setTextByVal();
                        return;
                    }
                    if(Array.isArray(res.data)){
                        res.data.forEach(v=>{
                            if(typeof v[showField]==='undefined'){
                                v[showField]='';
                            }
                            window.tableSelectIndexValues.value[props.field.url][v.id]=v;
                        })
                        setTextByVal();
                        return;
                    }

                    if(typeof res.data.current_page!=='undefined'){
                        res.data.data.forEach(v=>{
                            if(typeof v[showField]==='undefined'){
                                v[showField]='';
                            }
                            window.tableSelectIndexValues.value[props.field.url][v.id]=v;
                        })
                        setTextByVal();
                        return;
                    }
                    for(let k in res.data){
                        let v=res.data[k];
                        if(typeof v!=='string'&&typeof v!=='number'){
                            if(typeof v[showField]==='undefined'){
                                v[showField]='';
                            }
                            window.tableSelectIndexValues.value[props.field.url][v.id]=v;
                        }else{
                            window.tableSelectIndexValues.value[props.field.url][k]={
                                id:k,[showField]:v,
                            };
                        }
                    }
                    setTextByVal();
                })
            },{immediate:true,deep: true})

            return {
                text
            }
        },
        methods:{

        },
        template:`<div style="display: inline">
     <a-tooltip placement="topLeft">
        <template #title>
          <div>{{text}}</div>
        </template>
         <div style="display: initial">{{text}}</div>
    </a-tooltip>
</div>`,
    }
});