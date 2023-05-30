define([],function(){
    return {
        props:['info','field'],
        setup(props,ctx){
            console.log(props.info[props.field.name].split(/\n/));
        },
        computed:{

        },
        methods:{
        },
        template:`<div>
<template v-for="(item,index) in info[field.name].split(/\\n/)">
<template v-if="index>0"><br></template>
{{item}}
</template>
</div>`,
    }
});