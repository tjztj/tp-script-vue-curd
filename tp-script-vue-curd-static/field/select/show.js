define([],function(){
    return {
        props:['info','field'],
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
              if(typeof this.info['_Original_'+this.field.name]==='undefined'||this.info['_Original_'+this.field.name]===''){
                  return this.info[this.field.name]?[this.info[this.field.name]]:[];
              }
              const val=this.info['_Original_'+this.field.name].toString();
              if(val===this.field.nullVal.toString()){
                  return [];
              }
              return val.split(',');
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
        template:`<div>
                    <span v-if="info['_showText_'+field.name]" :style="oneStyle()">{{info['_showText_'+field.name]}}</span>
                    <template v-else>
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                    </template>
                </div>`,
    }
});