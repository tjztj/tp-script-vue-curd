define([],function(){
    return {
        props:['value'],
        computed:{
            modelVal:{
                get(){
                    return this.value;
                },
                set(val){
                    this.$emit('update:value',val.toString());
                }
            }
        },
        template:``,
    }
});