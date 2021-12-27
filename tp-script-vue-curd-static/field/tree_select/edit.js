define([],function(){
    const infos={};
    return {
        props:['field','value','validateStatus','form','info'],
        data(){
            return {
                val:undefined,
                treeExpandedKeys:[],
            }
        },
        mounted(){
            this.$nextTick(e=>{
                if(!this.field.multiple){
                    if(this.value!==''){
                        this.val={
                            value:this.value.toString(),
                            label:this.infos[this.value.toString()]?this.infos[this.value.toString()].title:'',
                        };
                    }
                }else{
                    const arr=typeof this.value==='string'?this.value.split(','):this.value;
                    const vals=[];

                    arr.forEach(v=>{
                        if(v.toString()!==''){
                            vals.push({
                                value:v.toString(),
                                label:this.infos[v.toString()]?this.infos[v.toString()].title:'',
                            })
                        }
                    })
                    this.val=vals;
                }
            })

        },
        computed:{
            treeData(){
                const doTreeItem=(arr)=>{
                    arr.map(item=>{
                        this.infos[item.value]=item;
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
                    }else if(val&&typeof val.value!=='undefined'){
                        value=val.value;
                    }
                    setTimeout(v=>{
                        this.setExpandedKeys(value);
                    },300)
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
            },
            setExpandedKeys(val){
                let that=this;
                function setExpanded(val,arr){
                    val=val.toString();
                    arr=arr||[];
                    if(!that.infos[val]){
                        return;
                    }
                    if(that.infos[that.infos[val].pvalue]&&!arr.includes(that.infos[val].pvalue)){
                        arr.push(that.infos[val].pvalue);
                        setExpanded(that.infos[val].pvalue,arr);
                    }
                }
                if(val){
                    const treeExpandedKeys=[];
                    val.split(',').forEach(v=>{
                        setExpanded(v,treeExpandedKeys);
                    })
                    this.treeExpandedKeys=treeExpandedKeys;
                }else{
                    this.treeExpandedKeys=[];
                }
            }
        },
        template:`<div class="field-box">
 <div class="l">
 <a-tree-select
 v-model:value="val"
 v-model:tree-expanded-keys="treeExpandedKeys"
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
  :show-checked-strategy="field.showCheckedStrategy"
  :filter-tree-node="filterTreeNode"
 >
 
</a-tree-select>
</div>
<div class="r"><span v-if="field.ext" class="ext-span">{{ field.ext }}</span></div>
</div>`
    }
});