define([],function(){
    return {
        props:['field','value'],
        data(){
            return {
                modelVal:[],
            }
        },
        watch:{
            value:{
                handler(value){
                    if(typeof value==='number'){
                        value=value.toString();
                    }
                    this.modelVal=value?value.split(','):[];
                },
                immediate:true,
            },
            modelVal(val){
                this.$emit('update:value',val.join(','));
            },
        },
        template:`<div class="field-box edit-padding-top-label">
                    <div class="l">
                        <a-checkbox-group v-model:model-value="modelVal"  :disabled="field.readOnly">
                        <transition-group name="bounce-min">
                            <template v-for="checkboxItem in field.items">
                                <a-checkbox :value="checkboxItem.value.toString()" v-show="checkboxItem.showItem===undefined||checkboxItem.showItem" v-if="!checkboxItem.hide"><span :style="{color:checkboxItem.color}">{{checkboxItem.text}}</span></a-checkbox>
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