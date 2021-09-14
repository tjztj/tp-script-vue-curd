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
        methods: {
            val(val){
                this.value=val;
                this.$emit('search',val);
            },
        },
        template:`<div>
                    <div class="filter-item-check-item" @click="val('')" :class="{active:value===''}"><div class="filter-item-check-item-value">全部</div></div>
                    <div v-for="(vo,key) in config.items" class="filter-item-check-item" @click="val(vo.value)" :class="{active:vo.value===value}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                </div>`,
    }
});