define([],function(){
    const valTitles={};
    return {
        props:['field','value','validateStatus','form','info'],
        data(){
          return {
              val:undefined,
          }
        },
        mounted(){
            this.$nextTick(e=>{
                if(!this.field.multiple){
                    this.val={
                        value:this.value.toString(),
                        label:valTitles[this.value.toString()]
                    };
                }else{
                    const arr=typeof this.value==='string'?this.value.split(','):this.value;
                    const vals=[];
                    arr.forEach(v=>{
                        vals.push({
                            value:v.toString(),
                            label:valTitles[v.toString()]
                        })
                    })
                    this.val=vals;
                }
            })

        },
        computed:{
            treeData(){
                 const doTreeItem=(arr)=>{
                    arr.map(item=>{
                        valTitles[item.value]=item.title;
                        if(item.children){
                            item.selectable=this.field.canCheckParent;
                            item.disableCheckbox=!this.field.canCheckParent;
                            item.children=doTreeItem(item.children);
                        }else{
                            item.selectable=typeof item.selectable==='undefined'?true:item.selectable;
                            item.disableCheckbox=false;
                        }
                        return item;
                    })
                    return arr;
                }
                return doTreeItem(JSON.parse(JSON.stringify(this.field.items)))
            }
        },
        watch:{
            val:{
                handler(val){
                    let value='';
                    if(Array.isArray(val)){
                        const arr=[];
                        val.forEach(v=>{
                            arr.push(v.value);
                        })
                        if(arr.length>0){
                            value=arr.join(',');
                        }
                    }else if(typeof val.value!=='undefined'){
                        value=val.value;
                    }
                    this.$emit('update:value',value);
                },
                deep:true
            }
        },
        methods:{
            filterTreeNode(inputValue,treeNode){
                inputValue=inputValue.trim();
                if(inputValue===''){
                    return true;
                }
                return treeNode.props.title.indexOf(inputValue)!==-1;
            }
        },
        template:`<div class="field-box">
 <div class="l">
 <a-tree-select
 v-model:value="val"
 :tree-data="treeData"
 :dropdown-style="{ maxHeight: '400px', overflow: 'auto' }"
 :placeholder="field.placeholder||'请选择'+field.title"
  :disabled="field.readOnly"
  tree-node-filter-prop="label"
  style="width: 100%"
  allow-clear
  show-search
  :label-in-value="true"
  :tree-checkable="field.multiple"
  :tree-check-strictly="field.multiple&&field.treeCheckStrictly"
  show-checked-strategy="SHOW_ALL"
  :filter-tree-node="filterTreeNode"
 >
 
</a-tree-select>
</div>
<div class="r"><span v-if="field.ext" class="ext-span">{{ field.ext }}</span></div>
</div>`
    }
});