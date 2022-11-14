define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    if(this.value===0||this.value==='0'){
                        return '';
                    }
                    if(/^\d+$/g.test(this.value.toString())){
                        return parseTime(Math.abs(this.value).toString().length<=10?this.value*1000:this.value,'{y}-{m}-{d}');
                    }
                    return this.value
                },
                set(val){
                    if(val===null){
                        this.$emit('update:value', '');
                        return;
                    }
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