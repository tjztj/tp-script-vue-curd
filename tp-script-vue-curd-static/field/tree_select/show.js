define([],function(){
    function getText(arr,val,pre){
        for(let i in arr){
            const item=arr[i];
            if(item.value.toString()===val.toString()){
                return pre+item.title.toString();
            }
            if(item.children){
                let title=getText(item.children,val,pre+item.title+'/')
                if(title){
                    return title;
                }
            }

        }
        return '';
    }

    return {
        props:['info','field'],
        computed:{
            showText(){
                const val=this.info[this.field.name];
                if(val===undefined||val===''){
                    return [];
                }
                const vals=[];
                val.split(',').forEach(v=>{
                    vals.push(getText(this.field.items,v,''))
                })

                return vals;

            },
        },
        template:`<div>
                    <div style="display: inline-block" v-if="!this.field.multiple">{{showText.length>0?showText.toString():''}}</div>
                    <div style="display: inline-block" v-else><div v-for="item in showText" style="display: inline-block;margin: 2px 4px;padding: 0 4px;border: 1px solid #d9d9d9;background-color: #fff;border-radius: 2px;">{{item}}</div></div>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});