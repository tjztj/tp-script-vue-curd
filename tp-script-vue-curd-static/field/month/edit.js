define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    if(/^\d+$/g.test(this.value.toString())){
                        return parseTime(this.value,'{y}-{m}');
                    }
                    return this.value
                },
                set(val){
                    this.$emit('update:value', val);
                }
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-month-picker v-model:value="val" :placeholder="field.placeholder||'请选择月份'" 
                        value-format="YYYY-MM" 
                        :disabled="field.readOnly" style="width: 100%"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});