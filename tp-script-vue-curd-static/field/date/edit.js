define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            dateDefaultValue:{
                get(){
                    if(!this.value){
                        this.$emit('update:value','');
                        return null;
                    }
                    let val='';
                    if(/^\-?\d+$/g.test(this.value.toString())){
                        //时间戳
                        val=parseTime(this.value,'{y}-{m}-{d}');
                        this.$emit('update:value',val);
                    }else{
                        val=this.value;
                    }
                    return moment(val);
                },
                set(val){
                    this.$emit('update:value',val.format('YYYY-MM-DD'));
                },
            },
        },
        methods:{
            disabledDate(val){
                if (!val) {
                    return false;
                }
                val.set('hours', 0)
                val.set('minutes', 0)
                val.set('seconds', 0)
                let values=val.unix()
                if(this.field.min!==null&&values<this.field.min){
                    return true;
                }
                if(this.field.max!==null&&values>this.field.max){
                    return true;
                }
                return false;
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-date-picker
                            v-model:value="dateDefaultValue"
                            type="date"
                            :placeholder="field.placeholder||'请选择日期'"
                             :disabled="field.readOnly"
                               :disabled-date="disabledDate"
                            style="width: 100%;"
                        />
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});