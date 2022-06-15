define([],function(){
    return {
        props:['record','field'],
        setup(props,ctx){
            const items={};
            props.field.items.forEach(item=>{
                items[item.value.toString()]=item;
            })
            return {
                items
            }
        },
        computed:{
            lists(){
                if(typeof this.record.record['_Original_'+this.field.name]==='undefined'||this.record.record['_Original_'+this.field.name].toString()===''){
                    return this.record.text?[this.record.text]:[];
                }
                return this.record.record['_Original_'+this.field.name].toString().split(',');
            }
        },
        methods:{
            color(val){
                if(val===undefined){
                    return;
                }
                if(!this.items[val.toString()]||!this.items[val.toString()].color){
                    return;
                }
                return this.items[val.toString()].color;
            },
            text(val){
                if(val===undefined){
                    return '';
                }
                if(!this.items[val.toString()]||!this.items[val.toString()].title){
                    return val;
                }
                return this.items[val.toString()].title;
            }
        },
        template:`<div style="display: inline">
                    <a-tooltip placement="topLeft" v-if="record.text">
                        <a-tooltip placement="topLeft"><template #title>
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                        </template>
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                        </a-tooltip>
                    </a-tooltip>
                </div>`,
    }
});