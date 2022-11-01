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
        template:`<div class="field-box">
                    <div class="l">
                       <a-year-picker v-model:value="val" :placeholder="field.placeholder||'请选择年份'" :disabled="field.readOnly" />
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>
`,
    }
});