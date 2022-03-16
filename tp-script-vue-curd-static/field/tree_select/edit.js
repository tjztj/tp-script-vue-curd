define([],function(){
    function getValToString(val){
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
        return value;
    }

    function closest(el, selector) {
        const matchesSelector = el.matches || el.webkitMatchesSelector || el.mozMatchesSelector || el.msMatchesSelector;

        while (el) {
            if (matchesSelector.call(el, selector)) {
                return el;
            } else {
                el = el.parentElement;
            }
        }
        return null;
    }

    function setCheckedDisabledStyle(){
        let docs=document.querySelectorAll('.a-tree-select-disabled-text');
        if(!docs){
            return;
        }
        docs.forEach(doc=>{
            let item=closest(doc,'.ant-select-selection-item')
            if(!item){
                return;
            }
            item.style.cursor='not-allowed';
            item.style.opacity='0.75';
            let clseItem=item.querySelector('.ant-select-selection-item-remove');
            if(clseItem){
                item.removeChild(clseItem)
            }
        })
    }

    return {
        props:['field','value','validateStatus','form','info'],
        data(){
            return {
                val:undefined,
                treeExpandedKeys:[],
                infos:{},
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

                if( this.val){
                    this.setExpandedKeys(getValToString(this.val));
                    this.$nextTick(e=>{
                        setCheckedDisabledStyle()
                    });
                }
            })

        },
        computed:{
            values:{
                set(val){
                    if(this.val){
                        //获取差集
                        let valKeys=[];
                        if(val){
                            for(let i in val){
                                valKeys[val.value]=i;
                            }
                            for(let i in this.val){
                                if(typeof valKeys[this.val[i].value]==='undefined'){
                                    if(this.infos[this.val[i].value]&&this.infos[this.val[i].value].disabled){
                                        val.splice(i,0,this.val[i]);
                                    }
                                }
                            }
                        }else{
                            const newval=[];
                            for(let i in this.val){
                                if(this.infos[this.val[i].value]&&this.infos[this.val[i].value].disabled){
                                    newval.push(this.val[i]);
                                }
                            }
                            if(newval.length>0){
                                val=newval;
                            }
                        }

                    }
                    this.val=val;
                    this.$emit('update:value',getValToString(val));
                    this.$nextTick(e=>{
                        setCheckedDisabledStyle()
                    });
                },
                get(){
                    return this.val;
                }
            },
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
                        item.customTitle=item.title;
                        item.title=undefined;
                        item.slots={ title: 'custom-title'};
                        return item;
                    })
                    return arr;
                }
                return doTreeItem(JSON.parse(JSON.stringify(this.field.items)))
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
                const that=this;
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
            },
            log(v){
                console.log(v)
            }
        },
        template:`<div class="field-box">
 <div class="l">
 <a-tree-select
 v-model:value="values"
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
   <template #custom-title="item">
   <span v-if="item.disabled" class="a-tree-select-disabled-text">{{item.customTitle}}</span>
   <span v-else>{{item.customTitle}}</spanv>
   </template>
</a-tree-select>
</div>
<div class="r"><span v-if="field.ext" class="ext-span">{{ field.ext }}</span></div>
</div>`
    }
});