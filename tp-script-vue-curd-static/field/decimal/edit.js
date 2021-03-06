define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            return {
                modelVal:Vue.ref(props.value)
            }
        },
        methods:{
          change(val){
              this.modelVal=val?parseFloat(Number(val).toFixed(this.field.precision)).toString():'';
              this.$emit('update:value',this.modelVal);
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