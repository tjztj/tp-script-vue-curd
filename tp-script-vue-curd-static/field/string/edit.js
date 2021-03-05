define([],function(){
    return {
        props:['field','value','validateStatus'],
        template:`<div><a-input v-model:value="value" :placeholder="field.placeholder||'请填写'+field.title" :suffix="field.ext" :disabled="field.readOnly"/></div>`,
    }
});