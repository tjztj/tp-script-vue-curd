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
    function getThisActionOhterWatchs(){
        return window.thisAction.watch||{};
    }
    function getThisActionOhterComputeds(){
        return window.thisAction.computed||{};
    }
    function getThisActionOhterSetup(props,ctx){
        let setup={},setupType=typeof window.thisAction.setup;
        if(setupType==='function'){
            setup=window.thisAction.setup(props,ctx)
        }else if(setupType==='object'){
            setup=window.thisAction.setup;
        }
        return setup;
    }
    ////--------------////




    function getStepNextOpenConfig(row,offset){
        if(!row.nextStepInfo){
            console.error('未发现下一步',row)
            return;
        }
        let title=vueData.title;
        title+=' [ '+row.nextStepInfo.title+' ]';
        let url=row.nextStepInfo.config.listBtnUrl;
        if(url.indexOf('?')>-1){
            url+='&id='+row.id;
        }else{
            url+='?id='+row.id;
        }
        const config={
            title:title,
            area:[row.nextStepInfo.config.listBtnOpenWidth, row.nextStepInfo.config.listBtnOpenHeight],
            content: url,
        };
        if(row.nextStepInfo.config.listBtnOpenHeight!=='100vh'){
            config.offset='auto'
        }
        return config;
    }



    actions.index=function(){
        const warnIcon=(Vue.openBlock(), Vue.createBlock("svg", {
            t: "1615779502296",
            class: "icon anticon",
            viewBox: "0 0 1024 1024",
            version: "1.1",
            xmlns: "http://www.w3.org/2000/svg",
            "p-id": "2345",
            width: "22",
            height: "22"
        }, [
            Vue.createVNode("path", {
                d: "M460.8 666.916571h99.693714v99.620572H460.8V666.916571z m0-398.482285h99.693714v298.861714H460.8V268.434286zM510.756571 19.382857C236.690286 19.382857 12.580571 243.565714 12.580571 517.485714c0 273.993143 221.622857 498.102857 498.102858 498.102857s498.102857-224.109714 498.102857-498.102857c0-273.92-224.182857-498.102857-498.102857-498.102857z m0 896.585143c-219.209143 0-398.482286-179.273143-398.482285-398.482286 0-219.136 179.346286-398.482286 398.482285-398.482285 219.136 0 398.482286 179.346286 398.482286 398.482285 0 219.209143-179.346286 398.482286-398.482286 398.482286z",
                fill: "#FF4343",
                "p-id": "2346"
            })
        ]));

        const infos={};
        return {
            setup(props,ctx){
                return getThisActionOhterSetup(props,ctx);
            },
            data(){
                let rowSelecteds=Vue.ref([]);
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
                    rowSelection:{
                        selectedRowKeys:rowSelecteds,
                        onChange(selectedRowKeys) {
                            rowSelecteds.value=selectedRowKeys;
                        },
                    },
                    fieldStepConfig:vueData.fieldStepConfig,
                    actionDefWidth:0,
                    indexUrl:VUE_CURD.CONTROLLER+'/index',
                    //其他配置
                    ...getThisActionOhterData(),
                }
            },
            mounted() {
                this.pageIsInit();
                this.fetch();
            },
            computed:{
                ...getThisActionOhterComputeds(),
                delSelectedIds(){
                    const ids=[];
                    this.rowSelection.selectedRowKeys.forEach(id=>{
                        if(typeof infos[id]==='undefined'){
                            return;
                        }
                        const record=infos[id];
                        if(!record.__auth||typeof record.__auth.show==='undefined'||record.__auth.show===true){
                            ids.push(id)
                        }
                    })
                    return ids;
                }
            },
            watch:{
                ...getThisActionOhterWatchs(),
            },
            methods:{
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
                    this.fetch();
                },
                fetch() {
                    this.loading = true;
                    this.$get(this.indexUrl,this.getWhere()).then(data => {
                        this.pagination.current=data.data.current_page;
                        this.pagination.total = data.data.total;
                        data.data.data.forEach(item=>{
                            infos[item.id]=item;
                        })
                        this.data = data.data.data;
                        this.loading = false;
                        this.refreshTableTirgger();
                    });
                },
                getWhere(){
                    const filter=JSON.parse(JSON.stringify(this.myFilters));
                    if(this.showFilter){
                        const filterData=this.$refs['filter'].getFilterData();
                        filter.filterData=filterData.filterData;
                        filter.childFilterData=filterData.childFilterData;
                    }else{
                        if(this.filterBase.filterValues){
                            filter.filterData=this.filterBase.filterValues;
                        }
                        if(this.childs){
                            let allFilterChildValues={};
                            this.childs.forEach(v=>{
                                if(v.filterData){
                                    allFilterChildValues[v.name]=v.filterData;
                                }
                            })
                            filter.childFilterData=allFilterChildValues;
                        }
                    }
                    if(vueData.baseInfo&&vueData.baseInfo.id){
                        filter.base_id=vueData.baseInfo.id;
                    }
                    return filter;
                },
                refreshTableTirgger(){
                    this.onDataLoad();//触发钩子
                    //列表加载完成
                    if(window.onListFetch){
                        Vue.nextTick(()=>{
                            window.onListFetch(this.data);
                        })
                    }
                },
                openAdd(){
                    this.openBox({
                        title:'新增 '+vueData.title,
                        offset:'rt',
                        content: vueData.editUrl,
                    }).end();
                },
                openEdit(row){
                    let title='修改 '+vueData.title;
                    if(row.stepInfo&&row.stepInfo.title){
                        title+=' [ '+row.stepInfo.title+' ]';
                    }
                    this.openBox({
                        title:title,
                        offset:'rt',
                        content: vueData.editUrl+'?id='+row.id,
                    }).end();
                },
                openNext(row){
                    this.openBox(getStepNextOpenConfig(row,'rt')).end();
                },
                openShow(row){
                    this.openBox({
                        title:'查看 '+vueData.title+' 相关信息',
                        offset:'rt',
                        content: vueData.showUrl+'?id='+row.id,
                    }).end();
                },
                delSelectedRows(e,delChilds){
                    this.loading = true;
                    this.$post(vueData.delUrl,{ids:this.delSelectedIds,delChilds:delChilds?1:0}).then(res=>{
                        antd.message.success(res.msg);
                        this.refreshTable();
                        this.rowSelection.selectedRowKeys=[];
                    }).catch(err=>{
                        this.loading = false;
                        if(!delChilds&&vueData.deleteHaveChildErrorCode&&err.errorCode==vueData.deleteHaveChildErrorCode){
                            antd.message.destroy();
                            const modal = antd.Modal.confirm({
                                content: '已有子数据，将删除下面所有子数据。确定删除所选数据及下面所有子数据？',
                                icon:warnIcon,
                                onOk:()=> {
                                    this.delSelectedRows(e,true)
                                },
                                onCancel:()=> {
                                    modal.destroy();
                                },
                            });
                        }
                    })
                },
                deleteRow(row,delChilds){
                    this.loading = true;
                    this.$post(vueData.delUrl,{ids:[row.id],delChilds:delChilds?1:0}).then(res=>{
                        antd.message.success(res.msg);
                        this.refreshTable();
                    }).catch(err=>{
                        this.loading = false;
                        if(!delChilds&&vueData.deleteHaveChildErrorCode&&err.errorCode==vueData.deleteHaveChildErrorCode){
                            antd.message.destroy();
                            const modal = antd.Modal.confirm({
                                content: '已有子数据，将删除下面所有子数据。确定删除本条数据及下面所有子数据？',
                                icon:warnIcon,
                                onOk:()=> {
                                    this.deleteRow(row,true)
                                },
                                onCancel:()=> {
                                    modal.destroy();
                                },
                            });
                        }
                    })
                },
                refreshTable(){
                    this.fetch();
                },
                refreshId(id){
                    this.loading = true;
                    const where=this.getWhere();
                    where.id=id;
                    where.page=1;
                    this.$get(this.indexUrl,where).then(data => {
                        if(!data.data.data[0]){
                            this.loading = false;
                            return;
                        }
                        //为了触发watch
                        let rows=[],isChange=false;
                        for(let i in this.data){
                            if(this.data[i].id==id){
                                rows.push(data.data.data[0])
                                isChange=true;
                            }else{
                                rows.push(this.data[i])
                            }
                        }
                        if(isChange===false){
                            this.loading = false;
                            return;
                        }
                        this.data=rows;
                        this.loading = false;
                        this.refreshTableTirgger();
                    });
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
                actionWidth(row){
                    return this.actionDefWidth
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
            form[field.name]=vueData.info&&typeof vueData.info[field.name]!=='undefined'?vueData.info[field.name]:'';
        })

        if(vueData.info&&vueData.info.id){
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
                    fieldHideList:{},
                }
            },
            computed:{
                showGroup(){
                    if(!this.haveGroup){
                        return false;
                    }
                    let showGroupNum=0;
                    for(let i in this.groupFields){
                        if(this.checkShowGroup(this.groupFields[i])){
                            if(showGroupNum>0){
                                return true;
                            }
                            showGroupNum++;
                        }
                    }
                    return false;
                }
            },
            methods:{
                onSubmit(option){
                    //我想要子组件可以不关闭当前窗口提交（就是自定义的字段可以新增数据后继续编辑）
                    option=option||{};

                    this.$refs.pubForm.validate().then(async() => {
                        for(let i in this.groupFields){
                            if(this.$refs['fieldGroup'+i]){
                                if(await this.$refs['fieldGroup'+i].validateListForm()===false){
                                    return;
                                }
                            }
                        }
                        if(option.showLoading!==false){
                            this.loading=true;
                        }
                        this.$post(window.location.href,this.form).then(async res=>{
                            parentWindow.antd.message.success(res.msg);
                            if(this.form&&this.form.id){
                                window.listVue.refreshId(this.form.id);
                            }else{
                                window.listVue.refreshTable();
                            }
                            if(!option.notClose){
                                this.close();
                            }else{
                                //因为数据有其他处理，所以这里返回过来的值就不再赋值到form中了
                                if(!this.form.id){
                                    this.form.id=res.data.info.id
                                }
                            }
                            if(option.success){
                                option.success(res)
                            }
                        }).finally(()=>{
                            if(option.showLoading!==false){
                                this.loading=false;
                            }
                            if(option.finally){
                                option.finally()
                            }
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
                        if(groupFieldItems[i].editShow&&!this.fieldHideList[groupFieldItems[i].name]){
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
                    fieldComponents
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
        let rowSelecteds=Vue.ref([]);
        const pagination={
            pageSize: vueData.indexPageOption.pageSize,
            sortField: '',
            sortOrder: '',
            showSizeChanger:vueData.indexPageOption.canGetRequestOption,
        }
        const infos={};
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
                    rowSelection:{
                        selectedRowKeys:rowSelecteds,
                        onChange(selectedRowKeys) {
                            rowSelecteds.value=selectedRowKeys;
                        },
                    },
                    fieldStepConfig:vueData.fieldStepConfig,
                    actionDefWidth:0,
                    indexUrl:window.location.href,
                }
            },
            mounted() {
                this.pageIsInit();
                this.fetch();
            },
            computed: {
                delSelectedIds(){
                    const ids=[];
                    this.rowSelection.selectedRowKeys.forEach(id=>{
                        if(typeof infos[id]==='undefined'){
                            return;
                        }
                        const record=infos[id];
                        if(!record.__auth||typeof record.__auth.show==='undefined'||record.__auth.show===true){
                            ids.push(id)
                        }
                    })
                    return ids;
                }
            },
            methods:{
                handleTableChange(pagination, filters, sorter) {
                    if(!pagination.pageSize){
                        return;
                    }
                    this.pagination=pagination;
                    this.pagination.sortField=sorter.field;
                    this.pagination.sortOrder=sorter.order;
                    this.fetch();
                },
                fetch() {
                    this.tableLoading = true;
                    this.$get(this.indexUrl,this.getWhere()).then(data => {
                        this.pagination.current=data.data.current_page;
                        this.pagination.total = data.data.total;
                        this.data = data.data.data
                        data.data.data.forEach(item=>{
                            infos[item.id]=item;
                        })
                        this.tableLoading = false;
                        //列表加载完成
                        this.refreshTableTirgger();//触发钩子
                    }).catch(err=>{
                        this.tableLoading = false;
                    });
                },
                getWhere(){
                    return {
                        pageSize: this.pagination.pageSize,
                        page:this.pagination.current,
                        sortField:this.pagination.sortField,
                        sortOrder:this.pagination.sortOrder,
                    };
                },
                refreshTableTirgger(){
                    this.onDataLoad();//触发钩子
                    //列表加载完成
                    if(window.onListFetch){
                        Vue.nextTick(()=>{
                            window.onListFetch(this.data);
                        })
                    }
                },
                openAdd(){
                    this.open()
                },
                openEdit(row){
                    this.open(row)
                },
                openNext(row){
                    this.openBox(getStepNextOpenConfig(row,'lt')).end();
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
                        title:'查看 详情',
                        offset: 'auto',
                        area: ['50vw', '72vh'],
                        content: vueData.showUrl+'?id='+row.id,
                    }).end();
                },
                delSelectedRows(){
                    this.tableLoading = true;
                    this.$post(vueData.delUrl,{ids:this.delSelectedIds}).then(res=>{
                        antd.message.success(res.msg);
                        this.refreshTable();
                        rowSelecteds.value=[];
                    }).catch(err=>{
                        this.tableLoading = false;
                    })
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
                refreshId(id){
                    this.loading = true;
                    const where=this.getWhere();
                    where.id=id;
                    where.page=1;
                    this.$get(this.indexUrl,where).then(data => {
                        if(!data.data.data[0]){
                            this.loading = false;
                            return;
                        }
                        //为了触发watch
                        let rows=[],isChange=false;
                        for(let i in this.data){
                            if(this.data[i].id==id){
                                rows.push(data.data.data[0])
                                isChange=true;
                            }else{
                                rows.push(this.data[i])
                            }
                        }
                        if(isChange===false){
                            this.loading = false;
                            return;
                        }
                        this.data=rows;
                        this.loading = false;
                        this.refreshTableTirgger();
                    });
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