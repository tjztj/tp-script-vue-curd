define([],function(){
    return {
        props:['field','value','validateStatus'],
        methods:{
            onRadioChange(e){

            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-radio-group v-model:value="value" @change="onRadioChange"
                         :disabled="field.readOnly">
                            <a-radio :value="radioItem.value"  v-for="radioItem in field.items">
                                {{radioItem.text}}
                            </a-radio>
                        </a-radio-group>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});