define([],function(){
    return {
        props:['field','value','validateStatus'],
        template:`<div class="field-box">
                    <div class="l">
                        <a-input-number v-model:value="value" :min="field.min" :max="field.max"
                                        :placeholder="field.placeholder||'输入整数'" :disabled="field.readOnly" style="width: 100%;"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});