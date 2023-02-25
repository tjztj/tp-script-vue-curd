define([],function(){
    return {
        props:['config'],
        setup(props,ctx){
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
        computed:{
            inputCheck(){
                if(this.inputValue===''){
                    return false;
                }
                for(let i in this.config.items){
                    if(this.config.items[i]==this.inputValue){
                        return false;
                    }
                }
                return true;
            },
            shortcuts(){
                let list=[];
                for(let i in this.config.items){
                    list.push({
                        label: this.config.items[i].title,
                        value: this.config.items[i].value,
                    })
                }
                return list;
            },

        },
        methods: {
            search(value){
                if(typeof value==="string"){
                    this.inputValue=value;
                }
                this.$emit('search',this.inputValue);
            },
        },
        template:`<div>
                <div class="input-value-div">
                    <a-week-picker style="width: 236px;" shortcuts-position="left" :shortcuts="shortcuts" @change="search" size="mini"/>
                </div>
</div>`,
    }
});