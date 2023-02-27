define([],function(){
    return {
        props:['record','field','list'],
        methods:{

        },
        template:`<div style="display: inline">
                    <template v-if="field.listEdit&&field.listEdit.saveUrl">
                        <a-input v-model="list[record.rowIndex].name" />
                    </template>
                    <template v-else>{{record.record[field.name]}}</template>
                </div>`,
    }
});