define(['/tp-script-vue-curd-static.php?listEdit/number.js'],function(listEdit){
    return {
        components:{
            listEdit,
        },
        props:['record','field','list'],
        setup:function (props,ctx){
        },
        computed:{

        },
        methods:{

        },
        template:`<list-edit :record="record" :field="field" v-model:list="list">{{record.record[field.name]}}</list-edit>`,
    }
});