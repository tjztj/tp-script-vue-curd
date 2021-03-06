define([],function(){
    return {
        props:['config'],
        setup(props,ctx){
            const range=Vue.ref([]);
            const onParentSearch = () => {
                if(props.config.activeValue&&props.config.activeValue.start&&props.config.activeValue.end){
                    range.value=[moment(props.config.activeValue.start),moment(props.config.activeValue.end)]
                }else{
                    range.value=[];
                    if(props.config.activeValue&&(props.config.activeValue.start||props.config.activeValue.end)){
                        ctx.emit('search',{
                            start:'',
                            end:'',
                        });
                    }
                }
            }
            onParentSearch();

            return {
                range,onParentSearch
            }
        },
        computed:{
            inputCheck(){
                if(this.range.length===0){
                    return false;
                }
                for(let i in this.config.items){
                    if(this.checked(this.config.items[i].start,this.config.items[i].end)){
                        return false;
                    }
                }
                return true;
            },
        },
        methods: {
            val(start,end){
                this.range=start===''&&end===''?[]:[moment(start),moment(end)];
                this.search();
            },
            checked(start,end){
                if(start===''&&end===''){
                    return this.range.length===0;
                }
                if(this.range.length===0){
                    return false;
                }
                return moment(start).unix()===this.range[0].unix()&&moment(end).unix()===this.range[1].unix();
            },
            search(){
                this.$emit('search',{
                    start:this.range[0]?this.range[0].format('YYYY-MM-DD'):'',
                    end:this.range[1]?this.range[1].format('YYYY-MM-DD'):'',
                });
            },
        },
        template:`<div>
                    <div class="filter-item-check-item" @click="val('','')" :class="{active:checked('','')}"><div class="filter-item-check-item-value">??????</div></div>
                            <div v-for="(vo,key) in config.items" class="filter-item-check-item" @click="val(vo.start,vo.end)" :class="{active:checked(vo.start,vo.end)}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                             <div class="filter-item-check-item filter-item-input-group" :class="{active:inputCheck}">
                                 <a-input-group compact size="small">
                                    <a-range-picker
                                      style="width: 210px"
                                        v-model:value="range"
                                        :placeholder="['????????????', '????????????']"
                                      />
                                       <a-button @click="search" size="small">??????</a-button>
                                 </a-input-group>
                             </div>
</div>`,
    }
});