define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            val:{
                get(){
                    if(this.field.multiple){
                        if(this.value===''){
                            return [];
                        }
                        if(typeof this.value==='number'){
                            return this.value.toString();
                        }
                        if(typeof this.value==='string'){
                            return this.value.split(',');
                        }
                        return [];
                    }
                    return this.value===''?undefined:this.value.toString();
                },
                set(val){
                    if(val===undefined){
                        this.$emit('update:value', '');
                        return;
                    }
                    this.$emit('update:value', typeof val==='object'?val.join(','):val);
                }
            },
            groupItems(){
                let items={};
                this.field.items.forEach(v=>{
                    if(!items[v.group]){
                        items[v.group]=[];
                    }
                    items[v.group].push(v);
                })
                return items;
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
                                  
                                  <template v-if="field.items&&field.items[0].group">
                                        <a-select-opt-group v-for="(items,key) in groupItems" :label="key">
                                              <a-select-option :value="optionItem.value" v-for="optionItem in items" v-show="!optionItem.hide">
                                                    {{optionItem.text}}
                                              </a-select-option>
                                        </a-select-opt-group>
                                   </template>
                                   <template v-else>
                                        <a-select-option :value="optionItem.value" v-for="optionItem in field.items" v-show="!optionItem.hide">
                                            {{optionItem.text}}
                                        </a-select-option>
                                    </template>
                        </a-select>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});