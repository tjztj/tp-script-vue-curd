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
        methods:{
            disabledDate(val) {
                if (!val) {
                    return false;
                }
                if (!this.field.showTime) {
                    val.set('hours', 0)
                    val.set('minutes', 0)
                    val.set('seconds', 0)
                }

                const values = val.unix()
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
                        <a-month-picker v-model:value="val" :placeholder="field.placeholder||'请选择月份'" 
                        value-format="YYYY-MM" 
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