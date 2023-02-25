define([],function(){
    const styleId='filter-index-time-field-style';
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
            const start=Vue.ref('');
            const end=Vue.ref('');
            const onParentSearch=function (){
                start.value=props.config.activeValue?props.config.activeValue.start:'';
                end.value=props.config.activeValue?props.config.activeValue.end:'';
            }
            onParentSearch();

            return {
                start,
                end,
                separator:'',
                onParentSearch,
            }
        },
        computed:{
            inputCheck(){
                if(this.start===''&&this.end===''){
                    return false;
                }
                for(let i in this.config.items){
                    if(this.config.items[i].start==this.start&&this.config.items[i].end==this.end){
                        return false;
                    }
                }
                return true;
            },
            modelVal:{
                get(){
                    return [this.start,this.end];
                },
                set(val){
                    this.start=val[0]||'';
                    this.end=val[1]||'';
                }
            }

        },
        methods: {
            val(start,end){
                this.start=start;
                this.end=end;
                this.search();
            },
            search(){
                this.$emit('search',{
                    start:this.start,
                    end:this.end,
                });
            },
            onParentSearch(){
                this.start=this.config.activeValue?this.config.activeValue.start:'';
                this.end=this.config.activeValue?this.config.activeValue.end:''
            }
        },
        template:`<div class="time-filter-box">
                    <div class="filter-item-check-item" @click="val('','')" :class="{active:start===''&&end===''}"><div class="filter-item-check-item-value">全部</div></div>
                        <div v-for="(vo,key) in config.items" class="filter-item-check-item" @click="val(vo.start,vo.end)" :class="{active:start==vo.start&&end==vo.end}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                        <div class="filter-item-check-item filter-item-input-group" :class="{active:inputCheck}">
                                <a-input-group>
                                      <a-time-picker size="mini"  type="time-range" v-model:model-value="modelVal" format="HH:mm" style="width: 150px"/>
                                      <a-button @click="search" type="primary" size="mini">确定</a-button>
                                </a-input-group>
                        </div>
</div>`,
    }
});