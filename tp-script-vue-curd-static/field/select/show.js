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
              return this.info['_Original_'+this.field.name].toString().split(',');
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
        template:`<div>
                    <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                </div>`,
    }
});