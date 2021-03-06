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
                            <a-checkbox :value="checkboxItem.value"  v-for="checkboxItem in field.items">
                                {{checkboxItem.text}}
                            </a-checkbox>
                        </a-checkbox-group>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});