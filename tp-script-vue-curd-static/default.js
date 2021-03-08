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
            data(){
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
                    myFilters:{
                        ...pagination,
                        page: 1,
                    },
                    showFilter:vueData.showFilter,
                    showTableTool:vueData.showTableTool,
                    canDel:vueData.canDel&&vueData.auth.del,
                    canEdit:vueData.canEdit&&vueData.auth.edit,
                    auth:vueData.auth,
                    childs:vueData.childs,
                    filterBase:{
                        filterConfig:vueData.filterConfig,
                        class:vueData.model,
                        name:vueData.modelName,
                        title:vueData.title,
                        filterValues:vueData.filter_data||{},//如果有值，filter-item不显示
                    },
                    //其他配置
                    ...getThisActionOhterData(),
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
                    const filter=JSON.parse(JSON.stringify(this.myFilters));
                    const filterData=this.$refs['filter'].getFilterData();
                    filter.filterData=filterData.filterData;
                    filter.childFilterData=filterData.childFilterData;


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
                    this.pagination.current = 1;
                    this.myFilters.page=1;
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