define([], function () {
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
        props: ['record', 'field'],
        computed:{
            info(){
                return this.record.record;
            },
            showText(){
                const val=this.info[this.field.name];
                if(val===undefined||val===''){
                    return [];
                }
                const vals=[];
                val.toString().split(',').forEach(v=>{
                    vals.push(getText(this.field.items,v,''))
                })

                return vals;

            },
        },
        template:`<div style="width: 100%;overflow: hidden; white-space: nowrap;  text-overflow: ellipsis;">
        <a-tooltip placement="topLeft">
        <template #title>
          <div style="display: initial" v-if="!this.field.multiple">{{showText.length>0?showText.toString():''}}</div>
          <div style="display: initial" v-else><div v-for="item in showText" style="display: inline-block;margin: 2px 4px;padding: 0 4px;border: 1px solid #d9d9d9;background-color: #e5e5e5;color:#333;border-radius: 2px;">{{item}}</div></div>
                    
        </template>
        <div style="display: initial" v-if="!this.field.multiple">{{showText.length>0?showText.toString():''}}</div>
        <div style="display: initial" v-else><div v-for="item in showText" style="display: inline-block;margin: 2px 4px;padding: 0 4px;border: 1px solid #d9d9d9;background-color: #fff;border-radius: 2px;">{{item}}</div></div>   
      </a-tooltip>
</div>`,
    }
});