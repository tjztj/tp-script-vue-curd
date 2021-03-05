define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            //todo
            if(props.value){
                props.value=[Math.floor(props.value/12),props.value%12];
            }else{
                props.value=['',''];
            }
            return {};
        },
        template:`<div class="field-box">
                   <div class="l year-month-field-box">
                        <a-input-group compact>
                            <a-input-number v-model:value="value[0]" min="0" max="999" placeholder="输入年数" :disabled="field.readOnly"/>
                            <a-input value="年" :disabled="true"/>
                            <a-input-number v-model:value="value[1]" min="0" max="12" placeholder="输入月数" :disabled="field.readOnly"/>
                            <a-input value="个月" :disabled="true"/>
                        </a-input-group>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});