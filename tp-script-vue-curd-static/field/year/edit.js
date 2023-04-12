define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    let val=this.value===null||this.value===undefined?'':this.value.toString();
                    return val==='0'?'':val
                },
                set(val){
                    if(val===undefined){
                        val='';
                    }
                    this.$emit('update:value', val);
                }
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                       <a-year-picker v-model="val" :placeholder="field.placeholder||'请选择年份'" :disabled="field.readOnly" />
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>
`,
    }
});