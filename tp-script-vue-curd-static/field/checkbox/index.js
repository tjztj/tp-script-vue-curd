define(['/tpscriptvuecurd/listEdit/select.js'],function(listEdit){
    return {
        components:{
            listEdit,
        },
        props:['record','field','list'],
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
        template:`<list-edit :record="record" :field="field" v-model:list="list" :multiple="true">
                    <span v-if="record.record['_showText_'+field.name]" :style="oneStyle()">{{record.record['_showText_'+field.name]}}</span>
                    <template v-else-if="record.record[field.name]">
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                    </template>
                </list-edit>`,
    }
});