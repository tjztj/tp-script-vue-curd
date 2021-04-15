define([],function(){
    return {
        props:['config'],
        setup(props,ctx){
            let val='';
            if(props.config.activeValue){
                val=props.config.activeValue;
                if(typeof val==='number'){
                    val=val.toString();
                }
            }
            return {
                inputValue:Vue.ref(val)
            }
        },
        methods: {
            search(){
                this.$emit('search',this.inputValue);
            },
        },
        template:`<div>
                <div class="input-value-div">
                     <a-input-group compact size="small">
                        <a-input v-model:value="inputValue" style="max-width: 188px" :placeholder="'填写 '+config.title+(config.type=='ValueFilter'?' 信息':' 关键字')"/>
                        <a-button @click="search" size="small">确定</a-button>
                     </a-input-group>
                </div>
</div>`,
    }
});