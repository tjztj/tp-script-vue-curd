define(['vueAdmin'], function (va) {
    let actions = {};
    ///////////////////////////////////////////////////////////////////////////////////////////////





    //----当前控制器其他配置---//
    window.thisAction=window.thisAction||{};
    function getThisActionOhterData(){
        let actionData={},actionDataType=typeof window.thisAction.data;
        if(actionDataType==='function'){
            actionData=window.thisAction.data()
        }else if(actionDataType==='object'){
            actionData=window.thisAction.data;
        }
        return actionData;
    }
    function getThisActionOhterMethods(){
        return window.thisAction.methods||{};
    }
    ////--------------////





    actions.index=function(){
        return {
            components:{
                'filter-item':{
                    data(){
                        return {
                            activeItemIndex:null,
                            inputGroup:{
                                start:'',
                                end:'',
                                separator:'',
                            },
                            inputValue:'',
                            radioValue:'',
                            range:[],
                            regionValue:[],
                            selectValue:null,
                        }
                    },
                    props:['config','filterItem'],
                    watch:{
                        activeItemIndex(val){
                            if(val===null){
                                this.filterItem.activeValue=null;
                            }else if(val===-1){
                                if(this.filterItem.type==='DateFilter'){
                                    this.filterItem.activeValue={
                                        start:this.range[0].format('YYYY-MM-DD'),
                                        end:this.range[1].format('YYYY-MM-DD'),
                                    }
                                }else if(this.filterItem.type==='MonthFilter'){
                                    this.filterItem.activeValue={
                                        start:this.range[0].format('YYYY-MM'),
                                        end:this.range[1].format('YYYY-MM'),
                                    }
                                }else{
                                    this.filterItem.activeValue={
                                        start:this.inputGroup.start,
                                        end:this.inputGroup.end,
                                    }
                                }
                            }else{
                                if(typeof this.filterItem.items[val]==='string'){
                                    this.filterItem.activeValue=this.filterItem.items[val]
                                }else if(this.filterItem.type==='RadioFilter'){
                                    this.filterItem.activeValue=this.filterItem.items[val].value;
                                }else{
                                    this.filterItem.activeValue={
                                        start:this.filterItem.items[val].start,
                                        end:this.filterItem.items[val].end,
                                    }
                                }
                            }
                            this.search();
                        }
                    },
                    mounted() {
                        if(this.filterItem.type == 'RegionFilter' && this.filterItem.regionTree.length===1  && this.filterItem.regionTree[0]['children'].length===1){
                            this.regionValue=[this.filterItem.regionTree[0]['id'],this.filterItem.regionTree[0]['children'][0]['id']];
                        }
                    },
                    methods:{
                        setActive(itemIndex){
                            this.activeItemIndex=itemIndex;
                            if(itemIndex===null){
                                this.inputGroup.start='';
                                this.inputGroup.end='';
                                this.range=[];
                            }else if(itemIndex!==-1){
                                if(this.filterItem.type==='DateFilter'){
                                    this.range=[moment(this.filterItem.items[itemIndex].start),moment(this.filterItem.items[itemIndex].end)];
                                }else if(this.filterItem.type==='MonthFilter'){
                                    this.range=[moment(this.filterItem.items[itemIndex].start),moment(this.filterItem.items[itemIndex].end)];
                                }else{
                                    this.inputGroup.start=this.filterItem.items[itemIndex].start;
                                    this.inputGroup.end=this.filterItem.items[itemIndex].end;
                                }
                            }
                        },
                        onInputGroupSearch(){
                            if(this.inputGroup.start===''&&this.inputGroup.end===''){
                                this.setActive(null);
                                return;
                            }
                            this.setActive(-1);
                        },
                        onInputValueSearch(value){
                            if(typeof value==="string"){
                                this.inputValue=value;
                            }
                            this.filterItem.activeValue=this.inputValue;
                            this.search();
                        },
                        onRangeSearch(){
                            if(this.range[0]||this.range[1]){
                                this.setActive(-1);
                            }else{
                                this.setActive(null);
                            }
                        },
                        onRegionChange(){
                            if(this.regionValue[1]){
                                this.filterItem.activeValue=this.regionValue[1];
                            }else if(this.regionValue[0]){
                                this.filterItem.activeValue=this.regionValue[0];
                            }else{
                                this.filterItem.activeValue=null;
                            }

                            this.search();
                        },
                        search(){
                            this.$emit('search')
                        },
                        filterOption(input, option) {
                            return option.props.title.toLowerCase().indexOf(input.toLowerCase()) >= 0;
                        },
                    },
                    template:`<div class="filter-item">
                    <div class="filter-item-l">{{filterItem.title}}</div>
                    <div class="filter-item-r">
                        <div v-if="filterItem.type=='BetweenFilter'">
                            <div class="filter-item-check-item" @click="setActive(null)" :class="{active:activeItemIndex===null}"><div class="filter-item-check-item-value">全部</div></div>
                            <div v-for="(vo,key) in filterItem.items" class="filter-item-check-item" @click="setActive(key)" :class="{active:key===activeItemIndex}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                            <div class="filter-item-check-item filter-item-input-group" :class="{active:activeItemIndex===-1}">
                                <a-input-group compact size="small">
                                      <a-input
                                        v-model:value="inputGroup.start"
                                        style="width: 80px; text-align: center"
                                        placeholder="开始值"
                                      />
                                      <a-input
                                        v-model:value="inputGroup.separator"
                                        style=" width: 30px; border-left: 0; pointer-events: none; backgroundColor: #fff"
                                        placeholder="~"
                                        disabled
                                      />
                                      <a-input
                                        v-model:value="inputGroup.end"
                                        style="width: 80px; text-align: center; border-left: 0"
                                        placeholder="结束值"/>
                                        <a-button @click="onInputGroupSearch" size="small">确定</a-button>
                                    </a-input-group>
                            </div>
                        </div>
                        <div v-else-if="filterItem.type=='RadioFilter'">
                            <div class="filter-item-check-item" @click="setActive(null)" :class="{active:activeItemIndex===null}"><div class="filter-item-check-item-value">全部</div></div>
                            <div v-for="(vo,key) in filterItem.items" class="filter-item-check-item" @click="setActive(key)" :class="{active:key===activeItemIndex}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                        </div>
                         <div v-else-if="filterItem.type=='ValueFilter'||filterItem.type=='LikeFilter'">
                             <div class="input-value-div">
                                 <a-input-group compact size="small">
                                    <a-input v-model:value="inputValue" style="max-width: 188px" :placeholder="'填写 '+filterItem.title+(filterItem.type=='ValueFilter'?' 信息':' 关键字')"/>
                                    <a-button @click="onInputValueSearch" size="small">确定</a-button>
                                 </a-input-group>
                            </div>
                         </div>
                         <div v-else-if="filterItem.type=='DateFilter'">
                            <div class="filter-item-check-item" @click="setActive(null)" :class="{active:activeItemIndex===null}"><div class="filter-item-check-item-value">全部</div></div>
                            <div v-for="(vo,key) in filterItem.items" class="filter-item-check-item" @click="setActive(key)" :class="{active:key===activeItemIndex}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                             <div class="filter-item-check-item filter-item-input-group" :class="{active:activeItemIndex===-1}">
                                 <a-input-group compact size="small">
                                    <a-range-picker
                                      style="width: 210px"
                                        v-model:value="range"
                                        :placeholder="['开始日期', '结束日期']"
                                      />
                                       <a-button @click="onRangeSearch" size="small">确定</a-button>
                                 </a-input-group>
                             </div>
                         </div>
                         <div v-else-if="filterItem.type=='RegionFilter'">
                             <div class="region-value-div">
                                <a-cascader
                                    v-model:value="regionValue"
                                    :options="filterItem.regionTree"
                                    placeholder="请选择村社"
                                    show-search
                                    size="small"
                                     change-on-select
                                    @change="onRegionChange"
                                />
                             </div>
                        </div>
                        <div v-else-if="filterItem.type=='SelectFilter'">
                             <div class="region-value-div">
                              <a-input-group compact size="small">
                                     <a-select style="width: 210px" 
                                              v-model:value="inputValue"
                                              allow-clear
                                              show-search 
                                              :filter-option="filterOption">
                                              <a-select-option value="">
                                              <span style="color: rgba(0,0,0,.35);">全部</span>
                                            </a-select-option>
                                            <a-select-option :value="optionItem.value" v-for="optionItem in filterItem.items" :title="optionItem.title">
                                                {{optionItem.title}}
                                            </a-select-option>
                                    </a-select>
                                     <a-button @click="onInputValueSearch" size="small">确定</a-button>
                                 </a-input-group>
                             </div>
                        </div>
                        <div v-else-if="filterItem.type=='WeekFilter'">
                             <div class="filter-item-check-item" @click="onInputValueSearch('')" :class="{active:inputValue===''}"><div class="filter-item-check-item-value">全部</div></div>
                            <div v-for="(vo,key) in filterItem.items" class="filter-item-check-item" @click="onInputValueSearch(vo.value)" :class="{active:vo.value===inputValue}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                             <div class="filter-item-check-item filter-item-input-group" :class="{active:activeItemIndex===-1}">
                                 <a-input-group compact size="small">
                                    <week-select v-model:value="inputValue" style="width: 305px"></week-select>
                                    <a-button @click="onInputValueSearch" size="small">确定</a-button>
                                 </a-input-group>
                             </div>
                        </div>
                        <div v-else-if="filterItem.type=='MonthFilter'">
                            <div class="filter-item-check-item" @click="setActive(null)" :class="{active:activeItemIndex===null}"><div class="filter-item-check-item-value">全部</div></div>
                            <div v-for="(vo,key) in filterItem.items" class="filter-item-check-item" @click="setActive(key)" :class="{active:key===activeItemIndex}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                             <div class="filter-item-check-item filter-item-input-group" :class="{active:activeItemIndex===-1}">
                                 <a-input-group compact size="small">
                                    <a-range-picker
                                      style="width: 210px"
                                        v-model:value="range"
                                        format="YYYY-MM"
                                        :placeholder="['开始月份', '结束月份']"
                                      />
                                       <a-button @click="onRangeSearch" size="small">确定</a-button>
                                 </a-input-group>
                             </div>
                         </div>
                        <div v-if="filterItem.type=='YearMonthFilter'">
                            <div class="filter-item-check-item" @click="setActive(null)" :class="{active:activeItemIndex===null}"><div class="filter-item-check-item-value">全部</div></div>
                            <div v-for="(vo,key) in filterItem.items" class="filter-item-check-item" @click="setActive(key)" :class="{active:key===activeItemIndex}"><div class="filter-item-check-item-value">{{vo.title}}</div></div>
                        </div>
                    </div>
                </div>`,
                }
            },
            data(){
                let filterConfig=vueData.filterConfig.map(function(v){
                    if(v.group){
                        v.title=v.group+' >'+v.title
                    }
                    return v;
                })
                let filterSource={filterConfig};
                for(let i in vueData.childs){
                    filterSource[vueData.childs[i].name]=vueData.childs[i].filterConfig.map(function(v){
                        if(v.group){
                            v.title=v.group+' >'+v.title
                        }
                        return v;
                    })
                }

                let modelTitles={
                    [vueData.model]:vueData.title,
                    [vueData.modelName]:vueData.title,
                };
                for(let i in vueData.childs){
                    modelTitles[vueData.childs[i].class]=vueData.childs[i].title;
                    modelTitles[vueData.childs[i].name]=vueData.childs[i].title;
                }


                const pagination={
                    pageSize: vueData.indexPageOption.pageSize,
                    sortField: '',
                    sortOrder: '',
                    showSizeChanger:vueData.indexPageOption.canGetRequestOption,
                }


                return {
                    listColumns:vueData.groupGroupColumns||{'':vueData.listColumns},
                    pagination: {...pagination},
                    loading: false,
                    data: [],
                    filterSource,
                    curdFilters:[],
                    curdChildFilters:{},
                    showMoreFilter:false,
                    myFilters:{
                        ...pagination,
                        filterData:{},
                        childFilterData:{},
                        page: 1,
                    },
                    filterValues:vueData.filter_data||{},//如果有值，filter-item不显示
                    showFilter:vueData.showFilter,
                    showTableTool:vueData.showTableTool,
                    canDel:vueData.canDel&&vueData.auth.del,
                    canEdit:vueData.canEdit&&vueData.auth.edit,
                    auth:vueData.auth,
                    childs:vueData.childs,
                    modelTitles,
                    //其他配置
                    ...getThisActionOhterData(),
                }
            },
            watch: {
                filterSource:{
                    handler(filterSource){
                        let curdFilters=[],childFilterDatas={},haveHide=false;
                        if(filterSource.filterConfig&&filterSource.filterConfig.length>0){
                            curdFilters=filterSource.filterConfig.filter(v=>v.show);
                            haveHide=curdFilters.length!==filterSource.filterConfig.length;
                        }
                        for(let key in filterSource){
                            if(key!=='filterConfig'){
                                if(filterSource[key].length>0){
                                    childFilterDatas[key]=filterSource[key].filter(v=>v.show);
                                    haveHide=haveHide|| childFilterDatas[key].length!==filterSource[key].length;
                                }
                            }
                        }

                        if(this.showMoreFilter===false&&haveHide){
                            this.showMoreFilter=true;
                        }
                        this.curdFilters=curdFilters;
                        this.curdChildFilters=childFilterDatas;
                    },
                    immediate:true,
                    deep: true,
                }
            },
            mounted() {
                this.pageIsInit();
                this.fetch();
            },
            methods:{
                handleTableChange(pagination, filters, sorter) {
                    this.pagination.current = pagination.current;
                    this.pagination.pageSize = pagination.pageSize;

                    this.myFilters.page=pagination.current;
                    this.myFilters.pageSize=pagination.pageSize;
                    this.myFilters.sortField=sorter.field;
                    this.myFilters.sortOrder=sorter.order;
                    this.fetch();
                },
                fetch() {
                    this.loading = true;
                    let filter=JSON.parse(JSON.stringify(this.myFilters));
                    filter.filterData=Object.assign(filter.filterData,this.filterValues);

                    let allFilterChildValues={};
                    this.childs.forEach(v=>{
                        allFilterChildValues[v.name]=v.filterData;
                    })
                    filter.childFilterData=Object.assign(filter.childFilterData,allFilterChildValues);

                    this.$get(VUE_CURD.CONTROLLER+'/index',filter).then(data => {
                        this.pagination.current=data.data.current_page;
                        this.pagination.total = data.data.total;
                        this.data = data.data.data.map( item=>{
                            // for (let name in item){
                            //     if(this.fieldObjs[name]){
                            //         switch (this.fieldObjs[name].type){
                            //             case 'WeekField':
                            //                 if(item[name]){
                            //                     let week=getLastWeek(item[name])
                            //                     let weekStr=week[0]+' ~ '+week[1];
                            //                     item[name]=getMonthWeek(item[name])+'（'+weekStr+'）';
                            //                 }
                            //                 break;
                            //         }
                            //     }
                            // }
                            return item;
                        });
                        this.onDataLoad();//触发钩子
                        this.loading = false;
                        //列表加载完成
                        if(window.onListFetch){
                            Vue.nextTick(function(){
                                window.onListFetch(this.data);
                            })
                        }
                    });
                },
                openAdd(){
                    this.openBox({
                        title:'新增 '+vueData.title,
                        offset:'rt',
                        content: vueData.editUrl,
                    }).end();
                },
                openEdit(row){
                    this.openBox({
                        title:'修改 '+vueData.title,
                        offset:'rt',
                        content: vueData.editUrl+'?id='+row.id,
                    }).end();
                },
                openShow(row){
                    this.openBox({
                        title:'查看 '+vueData.title+' 相关信息',
                        offset:'rt',
                        content: vueData.showUrl+'?id='+row.id,
                    }).end();
                },
                deleteRow(row){
                    this.loading = true;
                    this.$post(vueData.delUrl,{ids:[row.id]}).then(res=>{
                        antd.message.success(res.msg);
                        this.refreshTable();
                    }).catch(err=>{
                        this.loading = false;
                    })
                },
                refreshTable(){
                    this.fetch();
                },
                doFilter(){
                    let filterData={};
                    this.curdFilters.forEach(function(v){
                        if(typeof v.activeValue!=='undefined'&&v.activeValue!==null){
                            filterData[v.name]=v.activeValue;
                        }
                    })
                    let childFilterData={};
                    for(let key in this.curdChildFilters){
                        this.curdChildFilters[key].forEach(function(v){
                            if(typeof v.activeValue!=='undefined'&&v.activeValue!==null){
                                childFilterData[key]=childFilterData[key]||{};
                                childFilterData[key][v.name]=v.activeValue;
                            }
                        })
                    }


                    this.pagination.current = 1;
                    this.myFilters.page=1;
                    this.myFilters.filterData=filterData;
                    this.myFilters.childFilterData=childFilterData;
                    this.fetch()
                },
                openChildList(row,modelInfo){
                    this.openBox({
                        //TODO::标题要更详细
                        title:modelInfo.title,
                        offset:'rt',
                        content: modelInfo.listUrl+'?base_id='+row.id,
                    }).end();
                },
                downExcelTpl(){
                    window.open(vueData.downExcelTplUrl);
                },
                importExcelTpl(){
                    this.uploadOneFile({
                        url:vueData.importExcelTplUrl,
                        accept:"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel",
                        success:res=> {
                            antd.message.success(res.msg);
                            this.refreshTable()
                        }
                    }).trigger();
                },
                actionWidth(width){
                    return width
                },
                filterGroupIsShow(child){
                    for(let i in this.filterSource[child.name]){
                        if(this.filterGroupItemIsShow(this.filterSource[child.name][i],child)){
                            return true;
                        }
                    }
                    return false
                },
                filterGroupItemIsShow(item,child){
                    return item.show&&(!child.filterData||!child.filterData[item.name]);
                },
                onDataLoad(){
                    //数据获取完成钩子
                },

                ////其他配置
                ...getThisActionOhterMethods()
            }
        }
    };


    actions.edit=function(){
        let form={},validateStatus={},fieldObjs={};
        
        vueData.fields.forEach(function(field){
            fieldObjs[field.name]=field;
            form[field.name]=vueData.info?vueData.info[field.name]:'';
        })

        if(vueData.info){
            form.id=vueData.info.id;
        }
        return {
            data() {
                return {
                    loading:false,
                    haveGroup:vueData.groupFields?true:false,
                    groupFields:vueData.groupFields||{'':vueData.fields},
                    labelCol: { span: 4 },
                    wrapperCol: { span: 18 },
                    form:form,
                }
            },
            methods:{
                onSubmit(){
                    this.$refs.pubForm.validate().then(async() => {
                        for(let i in this.groupFields){
                            if(this.$refs['fieldGroup'+i]){
                                if(await this.$refs['fieldGroup'+i].validateListForm()===false){
                                    return;
                                }
                            }
                        }
                        this.loading=true;
                        this.$post(window.location.href,this.form).then(async res=>{
                            parentWindow.antd.message.success(res.msg);
                            window.listVue.refreshTable();
                            this.close();
                        }).finally(()=>{
                            this.loading=false;
                        })
                    }).catch(error => {
                        if(error.errorFields&&error.errorFields[0]&&error.errorFields[0].errors&&error.errorFields[0].errors[0]){
                            antd.message.warning(error.errorFields[0].errors[0])
                        }else{
                            antd.message.warning('请检测是否填写正确')
                        }
                        console.log('error', error);
                    });
                },
                close(){
                    document.body.dispatchEvent(new Event('closeIframe'));
                },
                checkShowGroup(groupFieldItems){
                    for(let i in groupFieldItems){
                        if(groupFieldItems[i].editShow){
                            return true;
                        }
                    }
                    return false;
                },
            }
        }
    };


    actions.show=function(){
        return {
            data(){
                return {
                    info:vueData.info,
                    haveGroup:vueData.groupFields?true:false,
                    groupFields:vueData.groupFields||{'':vueData.fields},
                }
            },
            methods:{
                checkShowGroup(groupFieldItems){
                    for(let i in groupFieldItems){
                        if(groupFieldItems[i].editShow){
                            return true;
                        }
                    }
                    return false;
                },
                showImages(imgs, start){
                    if(parseInt(start)!=start){
                        if(start){
                            let arr=typeof imgs==='string'?imgs.split('|'):imgs;
                            let index=arr.indexOf(start);
                            if(index!==-1){
                                start=index;
                                imgs=arr;
                            }
                        }
                    }
                    window.top.showImages(imgs, start);
                },
            }

        }
    }



    actions.childList=function(){
        const pagination={
            pageSize: vueData.indexPageOption.pageSize,
            sortField: '',
            sortOrder: '',
            showSizeChanger:vueData.indexPageOption.canGetRequestOption,
        }
        return {
            data(){
                return {
                    data:vueData.list,
                    listColumns:vueData.groupGroupColumns||{'':vueData.listColumns},
                    info:vueData.info,
                    title:vueData.title,
                    tableLoading:false,
                    canDel:vueData.canDel&&vueData.auth.del,
                    canEdit:vueData.auth.edit,
                    auth:vueData.auth,
                    pagination,
                }
            },
            mounted() {
                this.pageIsInit();
                this.fetch();
            },
            methods:{
                handleTableChange(pagination, filters, sorter) {
                    this.pagination=pagination;
                    this.pagination.sortField=sorter.field;
                    this.pagination.sortOrder=sorter.order;
                    this.fetch();
                },
                fetch() {
                    this.tableLoading = true;
                    this.$get(window.location.href,{
                        pageSize: this.pagination.pageSize,
                        page:this.pagination.current,
                        sortField:this.pagination.sortField,
                        sortOrder:this.pagination.sortOrder,
                    }).then(data => {
                        this.pagination.current=data.data.current_page;
                        this.pagination.total = data.data.total;
                        this.data = data.data.data
                        this.onDataLoad();//触发钩子
                        this.tableLoading = false;
                        //列表加载完成
                        if(window.onListFetch){
                            Vue.nextTick(function(){
                                window.onListFetch(this.data);
                            })
                        }
                    }).catch(err=>{
                        this.tableLoading = false;
                    });
                },
                openAdd(){
                    this.open()
                },
                openEdit(row){
                    this.open(row)
                },
                open(row){
                    this.openBox({
                        //TODO::标题要更详细
                        title:'<div style="font-size: 15px">'+(row?'修改':'新增')+' <span style="font-size: 14px;color: rgba(0,0,0,.55)">'+vueData.title+'</span> 单条数据</div>',
                        offset: 'auto',
                        area: ['50vw', '72vh'],
                        content: vueData.editUrl+'?base_id='+this.info.id+'&id='+(row?row.id:''),
                    }).end();
                },
                openShow(row){
                    this.openBox({
                        title:'查看 详细',
                        offset: 'auto',
                        area: ['50vw', '72vh'],
                        content: vueData.showUrl+'?id='+row.id,
                    }).end();
                },
                deleteRow(row){
                    this.tableLoading = true;
                    this.$post(vueData.delUrl,{ids:[row.id]}).then(res=>{
                        antd.message.success(res.msg);
                        this.refreshTable();
                    }).catch(err=>{
                        this.tableLoading = false;
                    })
                },
                refreshTable(){
                    this.fetch();
                },
                downExcelTpl(){
                    window.open(vueData.downExcelTplUrl);
                },
                importExcelTpl(){
                    this.uploadOneFile({
                        url:vueData.importExcelTplUrl,
                        accept:"application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel",
                        success:res=> {
                            antd.message.success(res.msg);
                            this.refreshTable()
                        }
                    }).trigger();
                },
                onDataLoad(){
                    //数据获取完成钩子
                },
            }

        };
    };


    actions.childEdit=function(){
        let returnData=actions.edit();
        let dataObj=returnData.data();

        returnData.data=function(){
            dataObj.form[vueData.parentField]=vueData.baseId;
            return dataObj;
        }



        return returnData;
    };



    ///////////////////////////////////////////////////////////////////////////////////////////////
    let return_actions = {};
    if(!actions[window.VUE_CURD.ACTION]&&window.ACTION){
        //方法可以直接写在页面中，不用写在这个js中也可以
        actions[window.VUE_CURD.ACTION]=window.ACTION;
    }
    for (let i in actions) return_actions[i] = function () {
        va(actions[i]())
    }
    return return_actions;
});