define([],function(){
    return {
        props:['config'],
        data(){
            return {
                range:[],
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
                    start:this.range[0]?this.range[0].format('YYYY-MM'):'',
                    end:this.range[1]?this.range[1].format('YYYY-MM'):'',
                });
            },
            handlePanelChange(val, mode){
                this.range = val;
            },
        },
        template:`<div>
                    <div class="filter-item-check-item" @click="val('','')" :class="{active:checked('','')}"><div class="filter-item-check-item-value">全部</div></div>
                            <div v-for="(vo,key) in config.items" class="filter-item-check-item" @click="val(vo.start,vo.end)" :class="{active:checked(vo.start,vo.end)}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                             <div class="filter-item-check-item filter-item-input-group" :class="{active:inputCheck}">
                                 <a-input-group compact size="small">
                                    <a-range-picker
                                      style="width: 210px"
                                        v-model:value="range"
                                        format="YYYY-MM"
                                        :mode="['month', 'month']"
                                        :placeholder="['开始月份', '结束月份']"
                                         @panelChange="handlePanelChange"
                                      />
                                       <a-button @click="search" size="small">确定</a-button>
                                 </a-input-group>
                             </div>
</div>`,
    }
});