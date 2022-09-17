define([],function(){
    return {
        props:['record','field'],
        computed:{
            lists(){
                if(typeof this.record.record['_Original_'+this.field.name]==='undefined'||this.record.record['_Original_'+this.field.name]===''){
                    return [];
                }
                return typeof this.record.record['_Original_'+this.field.name]==='undefined'?[]:this.record.record['_Original_'+this.field.name].toString().split(',');
            }
        },
        methods:{
          color(val){
              if(val===''||val===undefined){
                  return;
              }
              for(let key in this.field.items){
                  if(this.field.items[key].value.toString()===val.toString()){
                      if(this.field.items[key].color){
                          return this.field.items[key].color;
                      }
                  }
              }
          },
            text(val){
                if(typeof val==='undefined'||val===''){
                    return '';
                }
                for(let key in this.field.items){
                    if(this.field.items[key].value.toString()===val.toString()){
                        return this.field.items[key].title;
                    }
                }
                return val;
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
                    <a-tooltip placement="topLeft" v-if="record.record['_showText_'+field.name]">
                        <template #title>{{record.record['_showText_'+field.name]}}</template>
                        <span :style="oneStyle()">{{record.record['_showText_'+field.name]}}</span>
                    </a-tooltip>
                    <a-tooltip placement="topLeft" v-else-if="record.text">
                        <a-tooltip placement="topLeft"><template #title>
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                        </template>
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                        </a-tooltip>
                    </a-tooltip>
                </div>`,
    }
});