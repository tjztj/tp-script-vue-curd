define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    if(this.field.multiple){
                        if(typeof this.val==='string'||typeof this.val==='number'){
                            return this.value.toString.split(',');
                        }
                        return [];
                    }
                    return this.value;
                },
                set(val){
                    this.$emit('update:value', typeof val==='object'?val.join(','):val);
                }
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-select :mode="field.multiple?'multiple':'default'"
                                  :default-value="val"
                                  v-model:value="val"
                                  :placeholder="field.placeholder||'请选择'+field.title"
                                   :disabled="field.readOnly"
                                  show-search>
                            <a-select-option :value="optionItem.value" v-for="optionItem in field.items">
                                {{optionItem.text}}
                            </a-select-option>
                        </a-select>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});