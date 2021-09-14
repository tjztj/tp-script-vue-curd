define([],function(){
    return {
        props:['config'],
        setup(props,ctx){

            const value=Vue.ref('');

            const onParentSearch=function (){
                let val=props.config.activeValue||'';
                if(props.config.isMore){
                    if(typeof val==='number'){
                        val=val.toString();
                    }
                    if(val===''){
                        val=[];
                    }else if(typeof val==='string'){
                        val=props.config.activeValue.split(',')
                    }
                }
                value.value=val;
            };
            onParentSearch();


            return {
                value,
                onParentSearch
            }
        },
        methods: {
            val(val){
                if(!this.config.isMore){
                    this.value=val.toString();
                    this.$emit('search',val);
                    return;
                }

                if(val===''){
                    this.value=[];
                    this.$emit('search',[]);
                    return;
                }

                if(this.value.includes(val)){
                    this.value=this.value.filter(v=>v!==val);
                }else{
                    this.value.push(val.toString());
                }
                this.$emit('search',this.value);
            },
            isActive(val){
                if(!this.config.isMore){
                    return this.value===val;
                }

                if(val===''){
                    return this.value.length===0;
                }

                return this.value.includes(val);
            },
        },
        template:`<div>
                    <div class="filter-item-check-item" @click="val('')" :class="{active:isActive('')}"><div class="filter-item-check-item-value">全部</div></div>
                    <div v-for="(vo,key) in config.items" class="filter-item-check-item" @click="val(vo.value)" :class="{active:isActive(vo.value)}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
</div>`,
    }
});