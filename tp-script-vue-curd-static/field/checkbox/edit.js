define([],function(){
    return {
        props:['field','value','validateStatus'],
        data(){
            return {
                modelVal:[],
            }
        },
        watch:{
            value:{
                handler(value){
                    this.modelVal=value?value.split(','):[];
                },
                immediate:true,
            },
            modelVal(val){
                this.$emit('update:value',val.join(','));
            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-checkbox-group v-model:value="modelVal"  :disabled="field.readOnly">
                        <transition-group name="bounce-min">
                            <template v-for="checkboxItem in field.items">
                                <a-checkbox :value="checkboxItem.value" v-show="checkboxItem.showItem===undefined||checkboxItem.showItem" v-if="!checkboxItem.hide"><span :style="{color:checkboxItem.color}">{{checkboxItem.text}}</span></a-checkbox>
                            </template>
                        </transition-group>
                        </a-checkbox-group>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});