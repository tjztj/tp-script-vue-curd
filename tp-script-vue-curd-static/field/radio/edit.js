define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    return this.value===undefined?undefined:this.value.toString();
                },
                set(val){
                    this.$emit('update:value', val);
                }
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-radio-group v-model:value="val"
                         :disabled="field.readOnly">
                          <transition-group name="bounce-min">
                            <template v-for="radioItem in field.items">
                                <a-radio :value="radioItem.value" v-show="radioItem.showItem===undefined||radioItem.showItem" v-if="!radioItem.hide"><span :style="{color:radioItem.color}">{{radioItem.text}}</span></a-radio>
                            </template>
                           </transition-group>
                        </a-radio-group>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});