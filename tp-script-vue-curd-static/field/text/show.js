define([],function(){
    return {
        props:['info','field'],
        setup(props,ctx){
        },
        computed:{

        },
        methods:{
        },
        template:`<div>
<template v-for="(item,index) in info[field.name].split(/[\\n\\r]/g)">
<template v-if="index>0"><br></template>
{{item}}
</template>
</div>`,
    }
});