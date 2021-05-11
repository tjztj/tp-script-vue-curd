define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            const filterOption = (input, option) => {
                return option.title.toLowerCase().indexOf(input.toLowerCase()) >= 0;
            };
            return {
                filterOption
            }
        },
        computed:{
            val:{
                get(){
                    console.log(this.value);
                    const nullValue=typeof this.field.nullVal==='string'||typeof this.field.nullVal==='number'?this.field.nullVal.toString():null;

                    if(this.field.multiple){
                        if(this.value===''){
                            return [];
                        }
                        if(typeof this.value==='number'&&(nullValue===null||this.value.toString()!==nullValue)){
                            return this.value.toString();
                        }
                        if(typeof this.value==='string'&&(nullValue===null||this.value!==nullValue)){
                            return this.value.split(',');
                        }
                        return [];
                    }
                    return typeof this.value==='undefined'||this.value===''||(nullValue!==null&&this.value.toString()===nullValue)?undefined:this.value.toString();
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
                    if(v.showItem===false||v.hide){
                        return;
                    }
                    v.group=v.group||'';
                    if(!items[v.group]){
                        items[v.group]=[];
                    }
                    items[v.group].push(v);
                })
                return items;
            },
            haveGroup(){
                for(let i in this.field.items){
                    if(this.field.items[i].group){
                        return true;
                    }
                }
                return false;
            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-select :mode="field.multiple?'multiple':'default'"
                                  :default-value="val"
                                  v-model:value="val"
                                  :placeholder="field.placeholder||'请选择'+field.title"
                                   :disabled="field.readOnly"
                                   :filter-option="filterOption"
                                  show-search>
                                  
                                  <template v-if="haveGroup">
                                         <template v-for="(items,key) in groupItems">
                                            <template v-if="key">
                                                 <a-select-opt-group :label="key">
                                                     <a-select-option v-for="optionItem in items" :value="optionItem.value" :key="optionItem.value" :label="optionItem.text" :title="optionItem.text"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-select-option>
                                                 </a-select-opt-group>
                                            </template>
                                             <template v-else>
                                                <a-select-option v-for="optionItem in items" :value="optionItem.value" :key="optionItem.value" :label="optionItem.text" :title="optionItem.text"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-select-option>
                                             </template>
                                         </template>
                                   </template>
                                   <template v-else>
                                        <template v-for="optionItem in field.items">
                                            <a-select-option :value="optionItem.value" :key="optionItem.value" :label="optionItem.text" :title="optionItem.text" v-if="(optionItem.showItem===undefined||optionItem.showItem)&&(!optionItem.hide)"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-select-option>
                                        </template>
                                    </template>
                        </a-select>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});