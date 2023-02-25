define([],function(){
    return {
        props:['field','value','validateStatus'],
        mounted(){

        },
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
                        <a-textarea v-model="val" :auto-size="{ minRows: field.rowMin, maxRows: field.rowMax }"
                                    :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});