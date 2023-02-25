define([],function(){
    return {
        props:['config'],
        setup(props,ctx){
            const range=Vue.ref([]);
            const onParentSearch = () => {
                if(props.config.activeValue&&props.config.activeValue.start&&props.config.activeValue.end){
                    range.value=[props.config.activeValue.start,props.config.activeValue.end]
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
            // onParentSearch();

            return {
                range,onParentSearch
            }
        },
        computed:{
            rangeShortcuts(){
                let list=[];
                for(let i in this.config.items){
                    list.push({
                        label: this.config.items[i].title,
                        value: [this.config.items[i].start,this.config.items[i].end],
                    })
                }
                return list;
            },
        },
        methods: {
            change(value,date,dateString){
                // console.log(value, date, dateString);
                this.$emit('search',{
                    start:value[0]||'',
                    end:value[1]||'',
                });
            },
        },
        template:`<div class="input-value-div">
                     <a-range-picker
                            v-model:model-value="range"
                             mode="month"
                            style="width: 236px; "
                            shortcuts-position="left"
                            :shortcuts="rangeShortcuts"
                            size="mini"
                            @change="change"
                      />
</div>`,
    }
});