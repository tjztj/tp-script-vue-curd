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
                    <div class="filter-item-check-item" @click="search('')" :class="{active:inputValue===''}"><div class="filter-item-check-item-value">全部</div></div>
                    <div v-for="(vo,key) in config.items" class="filter-item-check-item" @click="search(vo.value)" :class="{active:vo.value===inputValue}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                    <div class="filter-item-check-item filter-item-input-group" :class="{active:inputCheck}">
                         <a-input-group compact size="small">
                            <week-select v-model:value="inputValue" style="width: 305px"></week-select>
                            <a-button @click="search" size="small">确定</a-button>
                         </a-input-group>
                    </div>
                </div>
</div>`,
    }
});