define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            modelVal:{
                get(){
                    if(typeof this.value==='number'){
                        this.$emit('update:value',this.value.toString());
                    }
                    if(this.value===''||this.value===null||this.value===undefined){
                        return;
                    }
                    let val=parseInt(this.value);
                    return isNaN(val)?0:val;
                },
                set(val){
                    if(val===null||val===undefined||val===this.field.nullVal){
                        val='';
                    }
                    this.$emit('update:value',val.toString());
                }
            }
        },
        template:`<div>
                    <a-input-number v-model="modelVal" :min="field.min" :max="field.max" :hide-button="!!field.ext"
                            :placeholder="field.placeholder||'输入整数'" :disabled="field.readOnly" style="width: 100%;">
                        <template v-if="field.ext" #suffix>{{field.ext}}</template>
                    </a-input-number>
                </div>`,
    }
});