define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    if(/^\d+$/g.test(this.value.toString())){
                        return parseTime(this.value,'{y}-{m}-{d}');
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
                        <week-select v-model:value="val" :placeholder="field.placeholder" :disabled="field.readOnly"></week-select>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});