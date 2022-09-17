define([],function(){
    const encodeDoc=document.createElement("div");
    function HTMLEncode(html) {
        (encodeDoc.textContent != null) ? (encodeDoc.textContent = html) : (encodeDoc.innerText = html);
        return encodeDoc.innerHTML;
    }
    function getShowItemHtml(item){
        let title=HTMLEncode(item.title.toString());
        if(item.color){
            title='<span style="color:'+item.color+'">'+title+'</span>';
        }
        return title;
    }

    function getText(arr,val,pre){
        for(let i in arr){
            const item=arr[i];
            if(item.value.toString()===val.toString()){
                return pre+getShowItemHtml(item);
            }
            if(item.children){
                let title=getText(item.children,val,pre+getShowItemHtml(item)+'/')
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
                if(this.info['_showText_'+this.field.name]){
                    return this.info['_showText_'+this.field.name].toString();
                }
                const val=this.info[this.field.name];
                if(val===undefined||val===''){
                    return [];
                }
                let vals=[];
                val.toString().split(',').forEach(v=>{
                    vals.push(getText(this.field.items,v,''))
                })

                vals = vals.map((v) => {
                    if (this.field.justShowLast) {
                        const arr = v.split('|=|');
                        return arr[arr.length - 1];
                    }
                    return v.replace(/\|=\|/g, '/');
                });

                vals.filter(v => {
                    return v !== '';
                })


                return vals;

            },
        },
        template:`<div>
                    <div style="display: inline-block" v-if="!this.field.multiple">{{showText.length>0?showText.toString():''}}</div>
                    <div style="display: inline-block" v-else><div v-for="item in showText" style="display: inline-block;margin: 2px 4px;padding: 0 4px;border: 1px solid #d9d9d9;background-color: #fff;border-radius: 2px;" v-html="item"></div></div>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});