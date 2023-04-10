define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    if(this.value===0||this.value==='0'||this.value===undefined||this.value===null){
                        return '';
                    }
                    if(/^-?\d+$/g.test(this.value.toString())){
                        return parseTime(Math.abs(this.value).toString().length<=10?this.value*1000:this.value,'{y}-{m}-{d}');
                    }
                    return this.value
                },
                set(val){
                    if(val===null||val===undefined){
                        this.$emit('update:value', '');
                        return;
                    }
                    this.$emit('update:value', val);
                }
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-week-picker v-model="val" :placeholder="field.placeholder" :disabled="field.readOnly"></a-week-picker>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});