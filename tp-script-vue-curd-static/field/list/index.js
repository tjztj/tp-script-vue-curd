
define(['/tpscriptvuecurd/field/list/show.js'],function(listShow){
    const styleId='list-index-field-style';
    const style = `
<style id="${styleId}">
.curd-list-show-field-box .list-field-item{
border: 1px solid #d9d9d9;
box-shadow: 0 2px 5px 1px rgba(0, 0, 0, 0.08)
}
.field-group-fieldset>.row:nth-child(odd) .curd-show-field-box .list-field-item:hover{
    box-shadow:none;
    border: 1px solid #ffd591;
}
.curd-list-show-field-box .list-field-item+.list-field-item{
margin-top: 24px;
}
.curd-list-show-field-box .list-field-item-row{
display: flex;
padding: 6px;
}
.curd-list-show-field-box>div>.list-field-box>.list-field-item>.list-field-item-row:nth-child(even) {
    background-color: #fbfbff;
    border-top: 1px solid #e9f3ff;
    border-bottom: 1px solid #e9f3ff;
}
.curd-list-show-field-box>div>.list-field-box>.list-field-item>.list-field-item-row:nth-child(even) .curd-show-field-box .list-field-item{
    background-image: linear-gradient(to left, #fff, #fffcf9);
    border: 1px solid #ffd8bf;
    box-shadow: 0 2px 5px 1px rgba(255, 255, 255, 0.04)
}
.curd-list-show-field-box>div>.list-field-box>.list-field-item>.list-field-item-row:nth-child(even) .curd-show-field-box .list-field-item:hover{
    box-shadow:none;
    border: 1px solid #d9f7be;
}
.curd-list-show-field-box>div>.list-field-box>.list-field-item>.list-field-item-row:nth-child(even) .curd-show-field-box .list-field-item>div+div{
    border-top: 1px solid #ffe7ba;
}
.curd-list-show-field-box>div>.list-field-box>.list-field-item>.list-field-item-row:nth-child(even) .curd-show-field-box .list-field-item-row:hover{
    background-color: #fff;
    border-radius: 6px;
}
.curd-list-show-field-box .list-field-item-row-l{
    padding-right: .5em;
    width: 16.66666667%;
    color: #000;
    font-weight: bold;
    letter-spacing: .08em;
    text-shadow: 1px 1px 1px rgb(0 0 0 / 8%);
    text-align: right;
}
.curd-list-show-field-box .list-field-item-row-r{
    flex: 1;
}
</style>
`;


    return {
        components:{
            listShow
        },
        props:['record','field'],
        setup(props,ctx){
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }

            const canShow=Vue.ref(false);
            let addComponents={};
            if(props.record.record[props.field.name+'ShowComponentUrl']){
                window.fieldComponents=window.fieldComponents||{};
                for(let i in props.record.record[props.field.name+'ShowComponentUrl']){
                    if(!window.fieldComponents[i]&&props.record.record[props.field.name+'ShowComponentUrl'][i].jsUrl){
                        window.fieldComponents[props.record.record[props.field.name+'ShowComponentUrl'][i].name]=props.record.record[props.field.name+'ShowComponentUrl'][i].jsUrl;
                        addComponents[props.record.record[props.field.name+'ShowComponentUrl'][i].name]=props.record.record[props.field.name+'ShowComponentUrl'][i].jsUrl;
                    }
                }

            }



            let list=[];
            if(props.record.record&&props.record.record[props.field.name+'List']){
                list=Object.values(props.record.record[props.field.name+'List']);
            }
            return {
                list,
                canShow,
                visible:Vue.ref(false),
                addComponents
            }
        },
        methods:{
            afterVisibleChange(){
                if(this.canShow){
                    return;
                }
                let requires=Object.values(this.addComponents);
                if(requires.length>0){
                    require(requires,()=>{
                        this.canShow=true;
                        for(let componentName in this.addComponents){
                            window.app.component(componentName,typeof require(fieldComponents[componentName])==='function'?require(fieldComponents[componentName])():require(fieldComponents[componentName]))
                        }
                    })
                }else{
                    this.canShow=true;
                }
            },
        },
        template:`<div style="display: initial">
<a v-if="list.length>0" @click="visible=true">查看</a> 
<span v-else style="color: #d9d9d9">无</span>
 <a-drawer
    wrap-class-name="body-iframe-drawer"
    :title="field.title"
    placement="left"
    v-model:visible="visible"
    @open="afterVisibleChange"
    width="550px"
    style="left: 0"
    :footer="false"
    unmount-on-close
  >
     <div style="overflow: auto;padding: 24px"><div class="curd-list-show-field-box" v-if="canShow"><list-show :info="record.record" :field="field"></list-show></div><div v-else>加载中...</div></div>
  </a-drawer>

</div>`,
    }
});