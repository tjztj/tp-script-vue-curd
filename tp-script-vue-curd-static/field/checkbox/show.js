define([],function(){
    return {
        props:['info','field'],
        computed:{
          lists(){
              if(typeof this.info['_Original_'+this.field.name]==='undefined'||this.info['_Original_'+this.field.name]===''){
                  return [];
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
        template:`<div>
                    <span v-if="info['_showText_'+field.name]" :style="oneStyle()">{{info['_showText_'+field.name]}}</span>
                    <template v-else>
                        <template v-for="(item,key) in lists"><span :style="{color:color(item)}">{{text(item)}}</span><span v-if="lists[key+1]" style="padding: 0 4px">,</span></template>
                    </template>
                    
                </div>`,
    }
});