define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            let val;
            if(props.value){
                val=[Math.floor(props.value/12),props.value%12];
            }else{
                val=['',''];
            }

            val=Vue.ref(val);

            Vue.watch(val,function(val){
                let value=0;
                if(val[0]){
                    value+=val[0]*12;
                }
                if(val[1]){
                    value+=parseInt(val[1]);
                }
                ctx.emit('update:value', value.toString());
            },{
                deep:true
            })


            return {val};
        },
        template:`<div class="field-box">
                   <div class="l year-month-field-box">
                        <a-input-group>
                            <a-input-number v-model="val[0]" :min="0" :max="999" placeholder="输入年数" :disabled="field.readOnly"  allow-clear hide-button>
                                <template #suffix>年</template>
                            </a-input-number>
                            <a-input-number v-model="val[1]" :min="0" :max="12" placeholder="输入月数" :disabled="field.readOnly"  allow-clear hide-button>
                                <template #suffix>个月</template>
                            </a-input-number>
                        </a-input-group>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});