define([],function(){
    return {
        props:['field','value','validateStatus','form'],
        template:`<div class="field-box">
                    <div class="l">
                        <input :value="value" type="color" @change="$emit('update:value',$event.target.value)">
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});