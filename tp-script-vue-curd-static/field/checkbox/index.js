define([],function(){
    return {
        props:['record','field'],
        computed:{
            lists(){
                if(typeof this.record.record['_Original_'+this.field.name]==='undefined'||this.record.record['_Original_'+this.field.name]===''){
                    return [];
                }
                return this.record.record['_Original_'+this.field.name].toString().split(',');
            }
        },
        methods:{
          color(val){
              if(val){
                  for(let key in this.field.items){
                      if(this.field.items[key].value.toString()===val.toString()){
                          if(this.field.items[key].color){
                             return this.field.items[key].color;
                          }
                      }
                  }
              }
          },
        },
        template:`<div>
                    <a-tooltip placement="topLeft" v-if="record.text">
                        <a-tooltip placement="topLeft"><template #title>
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{item}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                        </template>
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{item}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                        </a-tooltip>
                    </a-tooltip>
                </div>`,
    }
});