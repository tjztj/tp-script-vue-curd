define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            if(props.value){
                if(/^\d+$/g.test(props.value.toString())){
                    //时间戳
                    //todo
                    props.value=parseTime(props.value,'{y}-{m}-{d}');
                }
            }
            return {};
        },
        template:`<div class="field-box">
                    <div class="l">
                        <week-select v-model:value="value" :placeholder="field.placeholder" :disabled="field.readOnly"></week-select>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});