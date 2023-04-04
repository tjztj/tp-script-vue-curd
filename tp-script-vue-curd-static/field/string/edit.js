define([],function(){
    return {
        props:['field','value','validateStatus'],
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
        methods:{
            keyupDelete(){
                if(this.val.indexOf('*')===-1||!this.field.disengageSensitivity){
                    return;
                }
                this.val='';
            }
        },
        template:`<div><div class="read-only-just-show-text edit-padding-top-label" v-if="field.readOnly&&field.readOnlyJustShowText">{{val}}</div><a-input v-else v-model="val" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly" :allow-clear="field.disengageSensitivity" @keyup.delete="keyupDelete">
                    <template v-if="field.ext" #suffix>{{field.ext}}</template>
                </a-input></div>`,
    }
});