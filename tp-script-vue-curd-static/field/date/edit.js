define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            let dateDefaultValue=Vue.ref(null);
            if(props.value){
                if(/^\-?\d+$/g.test(props.value.toString())){
                    //时间戳
                    dateDefaultValue.value=parseTime(props.value,'{y}-{m}-{d}');
                    props.value=dateDefaultValue.value;
                    //TODO::测试代码是否有效
                    this.$emit('update:value',dateDefaultValue.value);
                }else{
                    dateDefaultValue.value=value;
                }
                dateDefaultValue.value=moment(dateDefaultValue.value);
            }
            return {
                dateDefaultValue
            }
        },
        methods:{
            dateChange(date){
                this.$emit('update:value',date.format('YYYY-MM-DD'));
            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-date-picker
                            v-model:value="field.dateDefaultValue"
                            type="date"
                            :placeholder="field.placeholder||'请选择日期'"
                             :disabled="field.readOnly"
                            style="width: 100%;"
                            @change="dateChange"
                        />
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});