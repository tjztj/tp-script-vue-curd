define([],function(){
    return {
        props:['field','value','validateStatus','form'],
        template:`<div class="field-box">
                    <div class="l">
                        <input :value="value" type="color" @change="$emit('update:value',$event.target.value)">
                        <span style="color: #8c8c8c;padding-left: 8px;;font-size: 12px">HEX颜色代码：<span v-if="value">{{value}}</span><span v-else style="color: #bfbfbf">未选择颜色</span></span>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});