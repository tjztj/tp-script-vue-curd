define([],function(){
    return {
        props:['info','field'],
        computed:{
          lists(){
              if(typeof this.info['_Original_'+this.field.name]==='undefined'||this.info['_Original_'+this.field.name]===''){
                  return [];
              }
              return this.info['_Original_'+this.field.name].toString().split(',');
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
            }
        },
        template:`<div>
                    <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                </div>`,
    }
});