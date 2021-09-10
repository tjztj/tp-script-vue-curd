define([],function(){
    const styleId='list-index-field-style';
    const style = `
<style id="${styleId}">
.filter-input-field-box .ant-input-group-sm span.ant-input-affix-wrapper{
    padding: 1px 7px;
}
</style>
`;

    return {
        props:['config'],
        setup(props,ctx){
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }
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
                <div class="input-value-div filter-input-field-box">
                     <a-input-group compact size="small">
                        <a-input v-model:value="inputValue" style="max-width: 188px;" :placeholder="'填写 '+config.title+(config.type=='ValueFilter'?' 信息':' 关键字')" allow-clear ></a-input>
                        <a-button @click="search" size="small">确定</a-button>
                     </a-input-group>
                </div>
</div>`,
    }
});