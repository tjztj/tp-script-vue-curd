define([],function(){
    function getValToString(val){
        let value='';
        if(Array.isArray(val)){
            const arr=[],haved={};
            val.forEach(v=>{
                if(!haved[v.value]){
                    arr.push(v.value);
                    haved[v.value]=true;
                }
            })
            if(arr.length>0){
                value=arr.join(',');
            }
        }else if(val&&typeof val.value!=='undefined'){
            value=val.value;
        }else if(typeof val==='string'){
            value=val;
        }
        return value;
    }
    return {
        props:['field','value','validateStatus','form','info'],
        data(){
            return {
                val:null,
                infos:{},
            }
        },
        mounted(){
            this.$nextTick(e=>{
                let val=this.value;
                if(val===null||val===undefined){
                    val='';
                }
                if(!this.field.multiple){
                    if(val!==''){
                        this.val={
                            value:val.toString(),
                            label:this.infos[val.toString()]?this.infos[val.toString()].title:'',
                        };
                    }
                }else{
                    const arr=typeof val==='string'?val.split(','):val;
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
            values:{
                set(val){
                    if(this.field.multiple&&this.val){
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
                },
                get(){
                    return this.val;
                }
            },
            treeData(){
                const doTreeItem=(arr,pTitles)=>{
                    pTitles=pTitles||[];
                    arr.map(item=>{
                        item.key=item.value;
                        this.infos[item.value]=item;
                        if(item.children){
                            item.selectable=this.field.canCheckParent;
                            if(!this.field.multiple){
                                item.disableCheckbox=!this.field.canCheckParent;
                            }
                            item.children=doTreeItem(item.children,[...pTitles,item.title]);
                        }else{
                            item.selectable=typeof item.selectable==='undefined'?true:item.selectable;
                            item.disableCheckbox=false;
                        }
                        item.pTitles=pTitles;
                        // item.customTitle=item.title;
                        // item.title=undefined;
                        // item.slots={ title: 'custom-title'};
                        return item;
                    })
                    return arr;
                }
                return doTreeItem(JSON.parse(JSON.stringify(this.field.items)))
            }
        },
        methods:{
            filterTreeNode(searchValue, nodeData) {
                if(typeof nodeData.title==='undefined'){
                    return false;
                }
                for(let v of [...nodeData.pTitles,nodeData.title]){
                    if(v.toLowerCase().indexOf(searchValue.toLowerCase()) > -1){
                        return true;
                    }
                }
                return false;
            },
            log(v){
                console.log(v)
            }
        },
        template:`<div class="field-box">
 <div class="l">
  <a-tree-select
    :data="treeData"
    v-model="values"
    :dropdown-style="{ maxHeight: field.dropdownMaxHeight+'px', overflow: 'auto' }"
    :disabled="field.readOnly"
    allow-search
    :filter-tree-node="filterTreeNode"
    label-in-value
    allow-clear
    :multiple="field.multiple"
    :tree-checkable="field.multiple"
    :tree-check-strictly="field.multiple&&field.treeCheckStrictly"
    :tree-checked-strategy="field.treeCheckedStrategy"
  ></a-tree-select>
</div>
<div class="r"><span v-if="field.ext" class="ext-span">{{ field.ext }}</span></div>
</div>`
    }
});