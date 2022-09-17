define([],function(){
    return {
        props:['info','field'],
        methods:{
          color(){
              if(typeof this.info['_Original_'+this.field.name]==='undefined'||this.info['_Original_'+this.field.name]===''){
                  return ;
              }
              for(let key in this.field.items){
                  if(this.field.items[key].value.toString()===this.info['_Original_'+this.field.name].toString()){
                      if(this.field.items[key].color){
                          return this.field.items[key].color;
                      }
                  }
              }
          },
        },
        template:`<div>
                    <span v-if="info['_showText_'+field.name]" :style="{color:color()}">{{info['_showText_'+field.name]}}</span>
                    <span v-else :style="{color:color()}">{{info[field.name]}}</span>
                </div>`,
    }
});