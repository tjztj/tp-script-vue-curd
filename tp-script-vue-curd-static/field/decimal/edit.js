define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){},
        computed:{
            modelVal:{
                get(){
                    let val=this.value;
                    if(typeof val==='undefined'||val===null){
                        val='';
                        this.$emit('update:value',val);
                    }else if(typeof val==='number'&&val!==val.toString()){
                        val=val.toString();
                        this.$emit('update:value',val);
                    }
                    return val;
                },
                set(val){
                    if(val===null||val===this.field.nullVal){
                        val='';
                    }
                    this.$emit('update:value',val.toString());
                }
            }
        },
        methods:{
            change(val){
                if(val===null||val===this.field.nullVal){
                    this.modelVal='';
                }else{
                    this.modelVal=parseFloat(Number(val).toFixed(this.field.precision)).toString();
                }
            }
        },
        template:`<div class="field-box">
                        <div class="l">
                            <a-input-number v-model:value="modelVal"
                                            :min="field.min"
                                            :max="field.max"
                                            :placeholder="field.placeholder||(field.precision?'保留'+field.precision+'位小数':'填入整数')"
                                            :disabled="field.readOnly"
                                            @change="change"
                                            style="width: 100%;"/>
                        </div>
                        <div class="r">
                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                        </div>
                    </div>`,
    }
});