define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    return this.value
                },
                set(val){
                    this.$emit('update:value', val);
                }
            }
        },
        template:`<div><a-input v-model:value="val" :placeholder="field.placeholder||'请填写'+field.title" :suffix="field.ext" :disabled="field.readOnly"/></div>`,
    }
});