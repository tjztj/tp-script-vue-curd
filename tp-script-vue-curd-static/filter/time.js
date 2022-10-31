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
            const value=Vue.ref('');
            const onParentSearch = () => {
                let val='';
                if(props.config.activeValue){
                    val=props.config.activeValue;
                    if(typeof val==='number'){
                        val=val.toString();
                    }
                }
                inputValue.value=val;
                value.value=val;
            }
            onParentSearch();

            return {
                inputValue,onParentSearch,val:value
            }
        },
        methods: {
            search(){
                this.$emit('search',this.inputValue);
            },
            openChange(open){
                if(open){
                    return;
                }
                if(this.val===this.inputValue){
                    return;
                }
                this.inputValue=this.val.toString();
                this.search();
            },
        },
        template:`<div>
                <div class="input-value-div filter-input-field-box">
                    <a-time-picker v-model:value="val" size="small" valueFormat="HH:mm:ss" @openChange="openChange"/>
                </div>
</div>`,
    }
});