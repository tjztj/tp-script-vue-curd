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
            },
            oneStyle(){
                if(this.lists.length>1){
                    return {};
                }
                let color=this.color(Object.values(this.lists)[0]);
                if(!color){
                    return {};
                }
                return {color:color};
            },
        },
        template:`<div style="display: inline">
                <template v-if="record.record['_showText_'+field.name]">
                     <a-tooltip placement="topLeft">
                        <template #title>{{record.record['_showText_'+field.name]}}</template>
                        <span :style="oneStyle()">{{record.record['_showText_'+field.name]}}</span>
                     </a-tooltip>
                </template>
                <template v-else-if="record.text">
                    <a-tooltip placement="topLeft">
                        <template #title>
                            <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                        </template>
                        <span>
                            <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                        </span>
                    </a-tooltip>
               </template>

                   
                </div>`,
    }
});