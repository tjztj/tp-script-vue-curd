define([],function(){
    const style=`<style id="table-select-field-style">
#table-select-popup-container{
width: 100%;
position: relative;
}
#table-select-popup-container .arco-scrollbar-container{
max-height: none;
overflow-y: hidden;
}
#table-select-popup-container .arco-scrollbar-track{
display: none;
}
.select-table-dropdown{
    margin: 0 4px;
    min-width: 100px;
}
.select-table-dropdown .arco-table-pagination{
    margin-right: 12px;
}
</style>`
    const infos={};
    const valDo=function (v){
        if(parseInt(v).toString()===v.toString()){
            return parseInt(v);
        }
        return v;
    };
    const pageSize=5;
    return {
        props:['field','value','validateStatus'],
        data(){
            return {
                data:[],
                pagination:{
                    pageSize,
                    total:0,
                    current:1,
                    showPageSize:true,
                    pageSizeOptions:['5','8','10','15','30','50']
                },
                myFilters:{
                    pageSize,
                    page: 1,
                    keywords:'',
                },
                loading:false,
                columns:[],
                columnDefNum:0,
                opened:false,
                selectedRowKeys:[],
                show:false,
                id:'table-select-'+window.guid(),
                options:[],
                maxW:'95vw',
            }
        },
        created(){
            if(!document.querySelector('#table-select-field-style')){
                document.head.insertAdjacentHTML('beforeend',style)
            }
            if(!document.querySelector('#table-select-popup-container')){
                document.body.insertAdjacentHTML('beforeend','<div id="table-select-popup-container"></div>')
            }

            this.pagination.pageSize=this.field.pageSize;
            this.myFilters.pageSize=this.field.pageSize;

            let columns=[];
            for(let i in  this.field.fields){
                columns.push({
                    title: this.field.fields[i],
                    ellipsis: true,
                    tooltip: true,
                    dataIndex: i,
                    key: i,
                })
            }
            this.columns=columns;
            this.columnDefNum=columns.length;
        },
        watch:{
            show(){
                this.setShowClass()
            },
            selectedRowKeys(selectedRowKeys){
                const options=[];
                selectedRowKeys.forEach(v=>{
                    options.push({
                        value: valDo(v),
                        label: infos[valDo(v)][this.showField],
                    })
                })
                this.options=options;
                this.$emit('update:value', this.getValueStr());
            },
            value:{
                handler(value, oldValue) {
                    value=value.toString().trim();
                    let selects=this.getValueStr();
                    if(selects===value){
                        return;
                    }
                    if(value===''||value==='undefined'){
                        this.selectedRowKeys=[];
                        this.options=[];
                        return;
                    }
                    this.loading=true;
                    let strVals=value.split(',');
                    let vals=strVals.map(valDo);
                    this.$post(this.field.url,{ids:vals}).then(res=>{
                        if(typeof res.data==='undefined'){
                            ArcoVue.Message.warning('字段'+this.field.title+'未正确获取到选择的信息-001');
                            return;
                        }
                        let options=[];
                        if(Array.isArray(res.data)&&typeof res.data[0]==='object'){
                            res.data.forEach(v=>{
                                if(!strVals.includes(v.id.toString())){
                                    return;
                                }
                                options.push({
                                    value: valDo(v.id),
                                    label: v[this.showField],
                                })
                                if(typeof infos[valDo(v.id)]==='undefined'){
                                    infos[valDo(v.id)]=v;
                                }
                            })
                        }else if(typeof res.data.current_page!=='undefined'){
                            res.data.data.forEach(v=>{
                                if(!strVals.includes(v.id.toString())){
                                    return;
                                }
                                options.push({
                                    value: valDo(v.id),
                                    label: v[this.showField],
                                })
                                if(typeof infos[valDo(v.id)]==='undefined'){
                                    infos[valDo(v.id)]=v;
                                }
                            })
                        }else{
                            for(let k in res.data){
                                let v=res.data[k];
                                if(typeof v!=='string'&&typeof v!=='number'){
                                    ArcoVue.Message.warning('字段'+this.field.title+'未正确获取到选择的信息-002');
                                    return;
                                }
                                if(!strVals.includes(k.toString())){
                                    continue;
                                }
                                options.push({
                                    value: valDo(k),
                                    label: v,
                                })
                                if(typeof infos[valDo(k)]==='undefined'){
                                    infos[valDo(k)]={
                                        [this.showField]:v
                                    };
                                }
                            }
                        }


                        this.loading=false;
                        this.options=options;
                        this.selectedRowKeys=vals;
                    });

                },
                immediate: true,
                deep: true
            },
        },
        computed:{
            disabled(){
                return this.field.readOnly||(this.loading&&this.show===false);
            },
            showField(){
                return Object.keys(this.field.fields)[0];
            },
        },
        methods:{
            getValueStr(){
                return this.selectedRowKeys.join(',');
            },
            '$post'(...params){
                return window.vueDefMethods.$post.call(this,...params,)
            },
            getList(){
                this.maxW= document.querySelector('#'+this.id).clientWidth+'px';

                this.loading=true;
                const params={...this.myFilters};
                if(!this.field.pageSize){
                    params.pageSize='';
                }
                this.$post(this.field.url,params).then(res=>{
                    let showActions=false,data=[];
                    if(params.pageSize||res.data.current_page){
                        this.pagination.current=res.data.current_page;
                        this.pagination.total = res.data.total;
                        data= res.data.data;
                    }else{
                        data=res.data;
                    }


                    data.forEach(item=>{
                        if(typeof item.id==='undefined'){
                            console.error('缺少id',item);
                        }
                        infos[valDo(item.id)]=item;
                        if(item.__actions&&item.__actions.length>0){
                            showActions=true;
                        }
                    })
                    if(showActions){
                        if(this.columnDefNum===this.columns.length){
                            this.columns.push({
                                titleSlotName:'custom-title-action',
                                slotName:'action',
                                fixed: 'right',
                            })
                        }
                    }else{
                        if((this.columnDefNum+1)===this.columns.length){
                            this.columns.pop();
                        }
                    }
                    this.data = data;
                    this.loading = false;
                }).catch(()=>{
                    this.loading = false;
                });
            },
            showDropdown(){
                if(this.disabled){
                    return;
                }
                this.show=true;
                if(this.opened){
                    return;
                }
                this.opened=true;
                this.loading=true;
                this.$nextTick(()=>this.getList());
            },
            pageChange(page){
                this.pagination.current = page;
                this.myFilters.page = page;
                this.getList();
            },
            pageSizeChange(pageSize){
                this.pagination.pageSize = pageSize;
                this.myFilters.pageSize = pageSize;
                this.pageChange(1);
            },
            sorterChange(dataIndex,direction){
                this.myFilters.sortField=dataIndex;
                this.myFilters.sortOrder=direction;
                this.pageChange(1);
            },

            setShowClass(){
                let inputClass=document.querySelector('#input-'+this.id).classList;
                if(this.show){
                    inputClass.add('arco-select-focused');
                    inputClass.add('arco-select-view-focus');
                }else{
                    inputClass.remove('arco-select-focused');
                    inputClass.remove('arco-select-view-focus');
                }
            },
            selectChange(vals){
                this.selectedRowKeys=typeof vals==='string'?(vals?[valDo(vals)]:[]):vals.map(valDo);
                this.$emit('update:value',this.getValueStr());
            },
            selectSearch(val){
                this.pagination.current = 1;
                this.myFilters.page=1;
                this.myFilters.keywords=val.toString().trim();
                this.getList();
            },
            refreshTable(){
                this.getList();
            },
            refreshId(id){
                this.refreshTable();
            },
            openBox(...params){
                return window.vueDefMethods.openBox.call(this,...params)
            },
            openOtherBtn(...params){
                // this.show=false;
                this.$nextTick(()=>{
                    window.vueDefMethods.openOtherBtn.call(this,...params)
                })
            },
        },
        template:`<div class="select-table-box" :id="id">
 <a-dropdown v-model:popup-visible="show" :disabled="disabled" :hide-on-select="false" popup-container="#table-select-popup-container">
 
  <a-select
    :model-value="selectedRowKeys"
    :multiple="field.multiple"
    style="width: 100%"
    :placeholder="field.placeholder||'请选择'+field.title"
    :disabled="disabled"
    allow-clear 
    @click="showDropdown" 
    :id="'input-'+id"
    :options="options"
    @change="selectChange"
    @search="selectSearch"
    @popup-visible-change="setShowClass"
    ref="select"
    allow-search
    :popup-visible="false"
  >
  </a-select>
 <template #content>
  <div class="select-table-dropdown" :style="{'max-width': maxW}" @click="setShowClass">
    <a-table row-key="id" :loading="loading" :columns="columns" :data="data" :pagination="field.pageSize>0?pagination:false" size="small"   v-model:selected-keys="selectedRowKeys" :row-selection="{showCheckedAll:true,type:field.multiple?'checkbox':'radio' }"  @page-change="pageChange" @page-size-change="pageSizeChange" @sorter-change="sorterChange">
        <template #custom-title-action>操作</template>
        <template #action="{ record }">   
            <template v-for="(btn,benKey) in record.__actions">
                <a-divider type="vertical" v-if="benKey>0"></a-divider>
                <a @click="openOtherBtn(btn,record)" :style="{color: btn.btnColor}">{{btn.btnTitle}}</a>
            </template>
       </template>
    </a-table>
  </div>
 </template>
</a-dropdown>
</div>`,
    }
});