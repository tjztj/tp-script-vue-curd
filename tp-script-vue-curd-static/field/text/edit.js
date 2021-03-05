define([],function(){
    return {
        props:['field','value','validateStatus'],
        template:`<div class="field-box">
                    <div class="l">
                        <a-textarea v-model:value="value" :auto-size="{ minRows: 2, maxRows: 5 }"
                                    :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});