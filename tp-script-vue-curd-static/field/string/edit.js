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
        template:`<div><span class="read-only-just-show-text" v-if="field.readOnlyJustShowText">{{val}}</span><a-input v-else v-model:value="val" :placeholder="field.placeholder||'请填写'+field.title" :suffix="field.ext" :disabled="field.readOnly"/></div>`,
    }
});