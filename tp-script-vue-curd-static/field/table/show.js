define(['/tpscriptvuecurd/field/table/edit.js'],function(tableEdit){
    return {
        components:{
            tableEdit,
        },
        props:['info','field'],
        data(){
            return {
                fieldHideList:{},
            }
        },
        template:`<div>
                   <table-edit v-model:value="info[field.name]" :field="field" :info="info" :disabled="true" :field-hideList="fieldHideList"></table-edit>
                </div>`,
    }
});