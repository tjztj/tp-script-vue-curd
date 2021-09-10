
define(['/tp-script-vue-curd-static.php?field/table/show.js'],function(tableShow){
    const styleId='table-field-index-field-style';
    const style = `
<style id="${styleId}">
.table-field-index-show-modal{
width: 80%;
max-width: 850px;
max-height: 92%;
}
</style>
`;

    return {
        components:{
            tableShow
        },
        props:['record','field'],
        setup(props,ctx){
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }

            let list=[];
            if(props.record.record&&props.record.record[props.field.name+'List']){
                list=props.record.record[props.field.name+'List'];
            }
            return {
                list,
                visible:Vue.ref(false),
            }
        },
        template:`<div>
<a v-if="list.length>0" @click="visible=true">查看</a> 
<span v-else style="color: #f0f0f0">无</span>
 <a-modal
    class="table-field-index-show-modal"
    :title="field.title"
    v-model:visible="visible"
    width="80%"
    :footer="null"
    destroy-on-close
  >
     <div><table-show :info="record.record" :field="field"></div>
  </a-drawer>

</div>`,
    }
});