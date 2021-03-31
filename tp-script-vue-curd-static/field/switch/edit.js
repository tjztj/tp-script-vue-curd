define([],function(){
    return {
        props:['field','value','validateStatus'],
        computed:{
            checked:{
                get(){
                    return this.value==this.field.items[1].value;
                },
                set(val){
                    this.$emit('update:value', val?this.field.items[1].value:this.field.items[0].value);
                }
            }
        },
        mounted() {
            if (!this.form.id) {
                //如果是新增页面
                if(this.field.items[1].default){
                    //如果选项默认选中
                    this.checked=true;
                }
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-switch v-model:checked="checked" :checked-children="field.items[1].title" :un-checked-children="field.items[0].title" :disabled="field.readOnly"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});