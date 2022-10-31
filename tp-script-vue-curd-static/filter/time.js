define([],function(){
    const styleId='filter-time-field-style';
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
                    <a-time-picker v-model:value="inputValue" size="small" @change="search"/>
                </div>
</div>`,
    }
});