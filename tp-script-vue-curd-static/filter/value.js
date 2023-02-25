define([],function(){
    const styleId='filter-index-field-style';
    const style = `
<style id="${styleId}">

</style>
`;

    return {
        props:['config'],
        setup(props,ctx){
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }
            const inputValue=Vue.ref('');
            const onParentSearch = () => {
                let val='';
                if(props.config.activeValue){
                    val=props.config.activeValue;
                    if(typeof val==='number'){
                        val=val.toString();
                    }
                }
                inputValue.value=val;
            }
            onParentSearch();

            return {
                inputValue,onParentSearch
            }
        },
        methods: {
            search(){
                this.$emit('search',this.inputValue);
            },
        },
        template:`<div>
                <div class="input-value-div filter-input-field-box">
                    <a-input-search style="max-width: 236px;" :placeholder="'填写 '+config.title+(config.type=='ValueFilter'?' 信息':' 关键字')" 
                        allow-clear 
                        button-text="确定" 
                        v-model:model-value ="inputValue"
                        size="mini"
                        @search="search"
                        search-button
                        />
                </div>
</div>`,
    }
});