define([],function(){
    return {
        props:['record','field'],
        methods:{
          color(){
              if(typeof this.record.record['_Original_'+this.field.name]==='undefined'||this.record.record['_Original_'+this.field.name]===''){
                  return;
              }
              for(let key in this.field.items){
                  if(this.field.items[key].value.toString()===this.record.record['_Original_'+this.field.name].toString()){
                      if(this.field.items[key].color){
                          return this.field.items[key].color;
                      }
                  }
              }
          },
        },
        template:`<div style="display: inline">
                    <a-tooltip placement="topLeft" v-if="record.text">
                        <a-tooltip placement="topLeft"><template #title><span :style="{color:color()}">{{record.text}}</span></template><span :style="{color:color()}">{{record.text}}</span></a-tooltip>
                    </a-tooltip>
                </div>`,
    }
});