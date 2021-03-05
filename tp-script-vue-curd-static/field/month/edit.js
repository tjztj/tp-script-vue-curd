define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            if(props.value){
                if(/^\d+$/g.test(props.value.toString())){
                    //时间戳
                    //todo
                    props.value=parseTime(props.value,'{y}-{m}');
                }
            }
            return {};
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-month-picker v-model:value="value" :placeholder="field.placeholder||'请选择月份'" 
                        value-format="YYYY-MM" 
                        :disabled="field.readOnly" style="width: 100%"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});