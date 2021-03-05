define([],function(){
    return {
        props:['field','value','validateStatus'],
        template:`<div class="field-box">
                        <div class="l">
                            <a-input-number v-model:value="value"
                                            :min="field.min"
                                            :max="field.max"
                                            :placeholder="field.placeholder||(field.precision?'保留'+field.precision+'位小数':'填入整数')"
                                            :disabled="field.readOnly"
                                            @change="value => form[field.name]=value?parseFloat(Number(value).toFixed(field.precision)):''"
                                            style="width: 100%;"/>
                        </div>
                        <div class="r">
                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                        </div>
                    </div>`,
    }
});