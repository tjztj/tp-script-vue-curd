define([],function(){
    return {
        props:['field','value','validateStatus','form'],
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
            if (!this.form.id&&this.value==='') {
                //如果是新增页面
                if(this.field.items[1].default){
                    //如果选项默认选中
                    this.checked=true;
                }else{
                    this.$emit('update:value', this.field.items[0].value);
                }
            }
        },
        template:`<div class="field-box" style="padding-top: 2px">
                    <div class="l">
                        <a-switch v-model="checked" :disabled="field.readOnly">
                            <template #checked>{{field.items[1].title}}</template>
                            <template #unchecked>{{field.items[0].title}}</template>
                        </a-switch>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});