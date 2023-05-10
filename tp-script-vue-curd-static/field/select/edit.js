define([],function(){
    return {
        props:['field','value'],
        setup(props,ctx){
            const filterOption = (input, option) => {
                return option.title.toLowerCase().indexOf(input.toLowerCase()) >= 0;
            };
            return {
                filterOption,
            }
        },
        computed:{
            val:{
                get(){
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
            option(){
                let groupItems={'':[]};
                let haveGroup=false;
                this.field.items.forEach(v=>{
                    if(v.showItem===false||v.hide){
                        return;
                    }
                    if(v.group){
                        haveGroup=true;
                    }else{
                        v.group='';
                    }
                    if(!groupItems[v.group]){
                        groupItems[v.group]=[];
                    }
                    v.label=v.title||v.text;
                    if(typeof v.value==='number'){
                        v.value=v.value.toString();
                    }
                    groupItems[v.group].push(v);
                });

                if(!haveGroup){
                    return groupItems[''];
                }

                let optionGroups=[];
                for(let n in groupItems){
                    if(n){
                        optionGroups.push(...groupItems[n]);
                    }else{
                        optionGroups.push({
                            isGroup:true,
                            label:n,
                            options:groupItems[n],
                        })
                    }
                }
                return optionGroups;
            },


        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-select :multiple="field.multiple"
                                  :default-value="val"
                                  v-model="val"
                                  :placeholder="field.placeholder||'请选择'+field.title"
                                   :disabled="field.readOnly"
                                   :filter-option="filterOption"
                                   :allow-clear="!field.required"
                                   allow-search
                                   :options="option"
                                   :virtual-list-props="{height:240}">
                                <template #option="vo">
                                  <span :style="{color:vo.data.color}">{{vo.data.label}}</span>
                                </template>
                                <template #label="vo">
                                  <span :style="{color:vo.data.color}">{{vo.data.label}}</span>
                                </template>
                        </a-select>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});