define([],function(){
    return {
        props:['info','field'],
        methods:{
          color(){
              if(this.info[this.field.name]){
                  for(let key in this.field.items){
                      if(this.field.items[key].value.toString()===this.info[this.field.name].toString()){
                          if(this.field.items[key].color){
                             return this.field.items[key].color;
                          }
                      }
                  }
              }
          },
        },
        template:`<div>
                    <span :style="{color:color()}">{{info[field.name]}}</span>
                </div>`,
    }
});