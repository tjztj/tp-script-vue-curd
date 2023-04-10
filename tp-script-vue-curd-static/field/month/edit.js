define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    if(this.value===0||this.value==='0'||this.value===null||this.value===undefined){
                        return '';
                    }
                    if(/^-?\d+$/g.test(this.value.toString())){
                        return parseTime(Math.abs(this.value).toString().length<=10?this.value*1000:this.value,'{y}-{m}');
                    }
                    return this.value
                },
                set(val){
                    if(val===null||val===undefined){
                        val='';
                    }
                    this.$emit('update:value', val);
                }
            }
        },
        methods:{
            disabledDate(val) {
                if (!val) {
                    return false;
                }
                val.setHours(0,0,0,0);
                val.setDate(1);

                const values = val.getTime()/1000
                if (this.field.min !== null && values < this.field.min) {
                    return true;
                }
                if (this.field.max !== null && values > this.field.max) {
                    return true;
                }
                return false;
            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-month-picker v-model:model-value="val" :placeholder="field.placeholder||'请选择月份'" 
                        :disabled="field.readOnly"
                         :disabled-date="disabledDate"
                         style="width: 100%"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});