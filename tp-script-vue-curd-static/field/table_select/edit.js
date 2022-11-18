define([],function(){
    const style=`<style id="table-select-field-style">
.select-table-dropdown{
    background-color: #fff;
    border-radius: 2px;
    box-shadow: 0 2px 8px rgb(0 0 0 / 15%);
}
.select-table-select-dropdown{
display: none!important;
}
</style>`
    const infos={};
    const pagination={
        pageSize: 5,
        sortField: '',
        sortOrder: '',
    };
    const valDo=function (v){
        if(parseInt(v).toString()===v.toString()){
            return parseInt(v);
        }
        return v;
    };
    return {
        props:['field','value','validateStatus'],
        data(){
            return {
                data:[],
                pagination:{...pagination,showSizeChanger:true,pageSizeOptions:['5','8','10','15','30','50']},
                myFilters:{
                    ...pagination,
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

            document.body.addEventListener('click',e=>{
                if(e.target&&!e.target.closest('#'+this.id)&&!e.target.closest('.'+this.id)){
                    this.show=false;
                }
                this.setShowClass();
            })

            let columns=[];
            for(let i in  this.field.fields){
                columns.push({
                    title: this.field.fields[i],
                    dataIndex: i,
                    key: i,
                    ellipsis:true,
                })
            }
            this.columns=columns;
            this.columnDefNum=columns.length;
        },
        watch:{
            show(){
                this.setShowClass()
            },
            value:{
                handler(value, oldValue) {
                    value=value.toString().trim();
                    let selects=this.getValueStr();
                    if(selects===value){
                        return;
                    }
                    if(value===''){
                        this.selectedRowKeys=[];
                        this.options=[];
                        return;
                    }
                    this.loading=true;
                    let strVals=value.split(',');
                    let vals=strVals.map(valDo);
                    this.$post(this.field.url,{ids:vals}).then(res=>{
                        if(typeof res.data==='undefined'){
                            antd.message.warning('字段'+this.field.title+'未正确获取到选择的信息-001');
                            return;
                        }
                        let options=[];
                        if(Array.isArray(res.data)){
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
                                    antd.message.warning('字段'+this.field.title+'未正确获取到选择的信息-002');
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
                this.$post(this.field.url,params).then(res=>{
                    this.pagination.current=res.data.current_page;
                    this.pagination.total = res.data.total;
                    let showActions=false;
                    res.data.data.forEach(item=>{
                        infos[valDo(item.id)]=item;
                        if(item.__actions&&item.__actions.length>0){
                            showActions=true;
                        }
                    })
                    if(showActions){
                        if(this.columnDefNum===this.columns.length){
                            this.columns.push({
                                slots: {customRender: 'action', title: 'custom-title-action'},
                                fixed: 'right',
                            })
                        }
                    }else{
                        if((this.columnDefNum+1)===this.columns.length){
                            this.columns.pop();
                        }
                    }

                    this.data = res.data.data;
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
            handleTableChange(pagination, filters, sorter) {
                if(!pagination.pageSize){
                    return;
                }
                this.pagination.current = pagination.current;
                this.pagination.pageSize = pagination.pageSize;

                this.myFilters.page=pagination.current;
                this.myFilters.pageSize=pagination.pageSize;
                this.myFilters.sortField=sorter.field;
                this.myFilters.sortOrder=sorter.order;
                this.getList();
            },
            onSelectChange (selectedRowKeys){
                this.selectedRowKeys = selectedRowKeys.map(valDo);
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
            setShowClass(){
                if(this.show){
                    if(document.querySelector('#input-'+this.id).closest('.ant-dropdown-open')){
                        document.querySelector('#input-'+this.id).closest('.ant-dropdown-open').classList.add('ant-select-focused');
                    }
                }else{
                    if(document.querySelector('#input-'+this.id).closest('.ant-dropdown-trigger')){
                        document.querySelector('#input-'+this.id).closest('.ant-dropdown-trigger').classList.remove('ant-select-focused');
                    }
                }
            },
            selectChange(vals,options){
                this.selectedRowKeys=vals.map(valDo);
                this.options=options.map(v=>{
                    v.value=valDo(v.value);
                    return v;
                });
                this.$emit('update:value',this.getValueStr());
            },
            selectSearch(val){
                if(window.event.type==='mousedown'&&window.event.target&&!window.event.target.closest('.ant-select-clear')){
                    return;
                }
                // console.log(window.event,window.event.target.closest('.ant-select-clear'));
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
                this.show=false;
                this.$nextTick(()=>{
                    window.vueDefMethods.openOtherBtn.call(this,...params)
                })
            },
        },
        template:`<div class="select-table-box" :id="id">
 <a-dropdown :trigger="['click']" :visible="show" :overlay-className="id" :disabled="disabled">
 
  <a-select
    :value="selectedRowKeys"
    :mode="field.multiple?'multiple':'default'"
    style="width: 100%"
    :placeholder="field.placeholder||'请选择'+field.title"
    :disabled="disabled"
    allow-clear 
    @click="showDropdown" 
    :id="'input-'+id"
    :options="options"
    @change="selectChange"
    @search="selectSearch"
    ref="select"
    :search-value="myFilters.keywords" 
    show-search
    :open="show"
    dropdown-class-name="select-table-select-dropdown"
  >
  </a-select>
 <template #overlay>
  <div class="select-table-dropdown" :style="{'max-width': maxW}">
    <a-table :row-key="record => record.id" :loading="loading" :columns="columns" :data-source="data" :pagination="pagination" @change="handleTableChange" size="small" :row-selection="{ selectedRowKeys: selectedRowKeys, onChange: onSelectChange,type:field.multiple?'checkbox':'radio' }">
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