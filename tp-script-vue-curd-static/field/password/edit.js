define([],function(){
    return {
        props:['field','value','validateStatus'],
        template:`<div class="field-box">
                    <div class="l">
                        <input type="text" 说明="不填充密码" style="height: 1px;width:1px;padding: 0;border: 0;opacity: 0.01;position: absolute">
                        <input type="password" 说明="不填充密码" style="height: 1px;width:1px;padding: 0;border: 0;opacity: 0.01;position: absolute">
                        <a-input-password v-model:value="value" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly"></a-input-password>
                    </div>
                    <div class="r"><span v-if="field.ext" class="ext-span">{{ field.ext }}</span></div>
                </div>`,
    }
});