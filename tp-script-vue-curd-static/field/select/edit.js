define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            //todo
            props.value=props.value?props.value.split(','):[];
            return {};
        },
        methods:{
            selectDefValue(){
                if(this.field.multiple&&typeof this.value==='string'){
                    return this.value?this.value.split(','):[];
                }
                return this.value;
            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-select :mode="field.multiple?'multiple':'default'"
                                  :default-value="selectDefValue(field)"
                                  v-model:value="value"
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