define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            return {
            }
        },
        computed:{
            modelVal:{
                get(){
                    let val=this.value;
                    if(typeof val==='undefined'||val===null){
                        val='';
                        this.$emit('update:value',val);
                    }else if(typeof val==='number'){
                        val=val.toString();
                        this.$emit('update:value',val);
                    }
                    
                    return val===''?'':parseFloat(val);
                },
                set(val){
                    if(val===null||val===undefined||val===this.field.nullVal){
                        val='';
                    }else{
                        val=parseFloat(Number(val).toFixed(this.field.precision)).toString();
                    }
                    this.$emit('update:value',val);
                }
            }
        },
        template:`<div>
                   <a-input-number v-model="modelVal"
                        :hide-button="!!field.ext"
                        :min="field.min"
                        :max="field.max"
                        :precision="field.precision"
                        :placeholder="field.placeholder||(field.precision?'保留'+field.precision+'位小数':'填入整数')"
                        :disabled="field.readOnly"
                        model-event="input"
                        style="width: 100%;">
                           <template v-if="field.ext" #suffix>{{field.ext}}</template>
                        </a-input-number>
                </div>`,
    }
});