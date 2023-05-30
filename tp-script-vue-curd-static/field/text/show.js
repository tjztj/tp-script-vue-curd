define([],function(){
    return {
        props:['info','field'],
        setup(props,ctx){

        },
        computed:{

        },
        methods:{
        },
        template:`<div v-html="info[field.name].replace(/[\n\r]/g,'<br>')"></div>`,
    }
});