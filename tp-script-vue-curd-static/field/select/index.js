define(['/tpscriptvuecurd/listEdit/select.js'],function(listEdit){
    return {
        components:{
            listEdit,
        },
        props:['record','field','list'],
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
                    return this.record.record[this.field.name]?[this.record.record[this.field.name]]:[];
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
        template:`<list-edit :record="record" :field="field" v-model:list="list" :multiple="field.multiple">
                    <span  v-if="record.record['_showText_'+field.name]" :style="oneStyle()">{{record.record['_showText_'+field.name]}}</span>
                    <span  v-else-if="record.record[this.field.name]">
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                    </span>
                </list-edit>`,
    }
});