define([],function(){
    return {
        props:['field','value','validateStatus','form'],
        setup(props,ctx){
            if(!props.field.readOnly&&props.field.editShow===true&&props.field.required===true&&!props.form.id&&!props.value){
                //如果是添加，且是必填，且为空
                if(props.field.regionTree.length===1){
                    if(!props.field.regionTree[0].children||props.field.regionTree[0].children.length===0){
                        //todo
                        formData[field.name]=[field.regionTree[0].id];
                    }else if(props.field.regionTree[0].children.length===1){
                        formData[field.name]=[field.regionTree[0].id,field.regionTree[0].children[0].id];
                    }
                }
            }
        },
        methods:{
            onRegionChange(){
                this.$emit('update:validateStatus','success');
            },
        },
        template:`<div class="field-box">
                   <template v-if="form.id">
                        <div class="l">
                            {{form[field.pField]}}/{{form[[field.cField]]}}
                        </div>
                    </template>
                    <template v-else>
                        <div class="l">
                            <a-cascader
                                v-model:value="value"
                                :options="field.regionTree"
                                :placeholder="field.placeholder||'请选择村社'"
                                show-search
                                 :disabled="field.readOnly"
                                @change="onRegionChange"
                            />
                        </div>
                        <div class="r">
                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                        </div>
                    </template>
                </div>`,
    }
});