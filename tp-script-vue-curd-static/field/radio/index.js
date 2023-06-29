define(['/tpscriptvuecurd/listEdit/select.js'],function(listEdit){
    return {
        components:{
            listEdit,
        },
        props:['record','field','list'],
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
        template:`<list-edit :record="record" :field="field" v-model:list="list" :multiple="false">
                     <span :style="{color:color()}" v-if="record.record['_showText_'+field.name]">{{record.record['_showText_'+field.name]}}</span>
                     <span :style="{color:color()}" v-else-if="record.record[field.name]">{{record.record[field.name]}}</span>
                </list-edit>`,
    }
});