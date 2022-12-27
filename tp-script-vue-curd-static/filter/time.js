define([],function(){
    const styleId='filter-index-time-field-style';
    const style = `
<style id="${styleId}">
.time-filter-box .ant-input.ant-input-disabled+.ant-time-picker .ant-time-picker-input{
border-left: 0;
}
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
                            <a-input-group compact size="small">
                                    <a-time-picker v-model:value="start" size="small" valueFormat="HH:mm:ss" placeholder="开始值" style="width: 82px; text-align: left"/>
                                      <a-input
                                        v-model:value="separator"
                                        style=" width: 30px; border-left: 0; pointer-events: none; background-color: #fff"
                                        placeholder="~"
                                        disabled
                                      />
                                   <a-time-picker v-model:value="end" size="small" valueFormat="HH:mm:ss" placeholder="结束值" style="width: 82px; text-align: left;"/>
                                    <a-button @click="search" size="small">确定</a-button>
                                </a-input-group>
                        </div>
</div>`,
    }
});