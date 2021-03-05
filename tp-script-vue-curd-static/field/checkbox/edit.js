define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            if(props.field.multiple){
                //todo
                props.value=props.value?props.value.split(','):[];
            }
            return {};
        },
        methods:{
            onCheckboxChange(e){

            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-checkbox-group v-model:value="value"  :disabled="field.readOnly">
                            <a-checkbox :value="checkboxItem.value"  v-for="checkboxItem in field.items" @change="onCheckboxChange">
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