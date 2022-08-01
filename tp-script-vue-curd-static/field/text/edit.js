define([],function(){
    return {
        props:['field','value','validateStatus'],
        mounted(){
            //让打开页面有数据时也能适应高度
            if(this.val!==''){
                const val=this.val;
                this.val=val+' ';
                setTimeout(()=>{
                    this.val=val;
                })
            }
        },
        computed:{
            val:{
                get(){
                    return this.value
                },
                set(val){
                    this.$emit('update:value', val);
                }
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-textarea v-model:value="val" :auto-size="{ minRows: field.rowMin, maxRows: field.rowMax }"
                                    :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});