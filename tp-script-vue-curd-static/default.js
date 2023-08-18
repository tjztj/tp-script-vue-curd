define(['vueAdmin'], function (va) {
    let actions = {};
    ///////////////////////////////////////////////////////////////////////////////////////////////


    const warnIcon=function(color,size){
        color=color||"#FF4343";
        size=size||36;
        return (Vue.openBlock(), Vue.createBlock("svg", {
            t: "1615779502296",
            class: "icon anticon",
            viewBox: "0 0 1024 1024",
            version: "1.1",
            xmlns: "http://www.w3.org/2000/svg",
            "p-id": "2345",
            width: size,
            height: size
        }, [
            Vue.createVNode("path", {
                d: "M460.8 666.916571h99.693714v99.620572H460.8V666.916571z m0-398.482285h99.693714v298.861714H460.8V268.434286zM510.756571 19.382857C236.690286 19.382857 12.580571 243.565714 12.580571 517.485714c0 273.993143 221.622857 498.102857 498.102858 498.102857s498.102857-224.109714 498.102857-498.102857c0-273.92-224.182857-498.102857-498.102857-498.102857z m0 896.585143c-219.209143 0-398.482286-179.273143-398.482285-398.482286 0-219.136 179.346286-398.482286 398.482285-398.482285 219.136 0 398.482286 179.346286 398.482286 398.482285 0 219.209143-179.346286 398.482286-398.482286 398.482286z",
                fill: color,
                "p-id": "2346"
            })
        ]))
    };

    const warnIconContent=function (content,iconColor,isOneRow){
        let boxStyle={};
        if(isOneRow){
            boxStyle.display='flex';
            boxStyle.alignItems='center';
            boxStyle.justifyContent='center '
        }else{
            boxStyle.textAlign='center';
        }

        return (Vue.openBlock(), Vue.createElementBlock("div", { style:boxStyle  }, [
            warnIcon(iconColor,isOneRow?24:36),
            isOneRow?Vue.createElementVNode("span", { style: {"padding-left":"12px"}}, content):Vue.createElementVNode("div", { style: {"margin-top":"12px"}}, content),
        ]));
    }



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
    function getThisActionOhterComponents(){
        return window.thisAction.components||{};
    }
    function getThisActionOhterProvides(vueObj){
        if(!window.thisAction.provide){
            return {};
        }
        return typeof window.thisAction.provide==='function'?window.thisAction.provide.call(vueObj):window.thisAction.provide;
    }

    ////--------------////




    function getStepOpenConfig(row,stepInfo){
        let title=vueData.title;
        title+=' [ '+stepInfo.title+' ]';
        let url=setUrlParams(stepInfo.config.listBtnUrl,{id:row.id});
        const config={
            title:title,
            area:[stepInfo.config.listBtnOpenWidth, stepInfo.config.listBtnOpenHeight],
            content: url,
        };
        if(stepInfo.config.listBtnOpenHeight!=='100vh'){
            config.offset='auto'
        }
        return config;
    }

    function getStepNextOpenConfig(row,offset){
        if(!row.nextStepInfo){
            console.error('未发现下一步',row)
            return;
        }
        const config=getStepOpenConfig(row,row.nextStepInfo);
        config.offset=config.offset||offset;
        return config;
    }

    function getStepJustDo(row,that){
        (top.ArcoVue||ArcoVue).Modal.warning({
            title: Vue.createVNode('b',{},'操作确认'),
            content: row.nextStepInfo.listDirectSubmit,
            hideCancel:false,
            onBeforeOk() {
                return new Promise((resolve, reject) => {
                    let url=setUrlParams(row.nextStepInfo.config.listBtnUrl,{id:row.id});
                    that.$post(url,{id:row.id}).then(res=>{
                        ArcoVue.Message.success(res.msg);
                        if(!res.data.refreshList&&that.refreshId){
                            that.refreshId(row.id);
                        }else{
                            that.refreshTable();
                        }
                        resolve();
                    }).catch(()=>{
                        reject()
                    });
                });
            }
        });
    }



    function setUrlParams(url,params){
        if(typeof params==='string'){
            if(url.indexOf('?')===-1){
                url+='?'+params;
            }else{
                url+='&'+params;
            }
        }else{
            for(let i in params){
                url=setUrlParams(url,i+'='+params[i]);
            }
        }
        return url;
    }


    function openParam(btnOption,defTitle,defUrl){
        let title=defTitle;
        let w=this.cWindow&&this.cWindow.w?this.cWindow.w:'45vw';
        let h=this.cWindow&&this.cWindow.h?this.cWindow.h:'100vh';
        let url=defUrl;
        let offset=this.cWindow&&this.cWindow.f?this.cWindow.f:'rt';
        if(btnOption){
            if(btnOption.modalTitle){
                title=btnOption.modalTitle;
            }
            if(btnOption.modalW){
                w=btnOption.modalW;
            }
            if(btnOption.modalH){
                h=btnOption.modalH;
            }
            if(btnOption.modalUrl){
                url=btnOption.modalUrl;
            }
            if(btnOption.modalOffset){
                offset=btnOption.modalOffset;
            }
        }

        w=w.toLowerCase();
        h=h.toLowerCase();



        return {
            title:title,
            offset:offset,
            area: [w.toLowerCase(), h.toLowerCase()],
            content: url,
        };
    }


    actions.index=function(){
        const fieldObjs={};
        for(let i in vueData.groupGroupColumns){
            vueData.groupGroupColumns[i].forEach(v=>{
                fieldObjs[v.name]=v;
            })
        }

        function getTreeParentKeys(tree){
            const returnArr=[];
            tree.forEach(v=>{
                if(v.children){
                    returnArr.push(v.value);
                    returnArr.push(...getTreeParentKeys(v.children));
                }
            })
            return returnArr;
        }

        let leftCate=vueData.leftCate||{show:false,list:[]};
        let leftCateShowTools={};
        let setLCST=function (tree){
            for(let i in tree){
                leftCateShowTools[tree[i].value]=false;
                if(tree[i].children){
                    setLCST(tree[i].children);
                }
            }
        };
        setLCST(leftCate.list)


        const infos={};
        const setInfos=function (list){
            list.forEach(v=>{
                infos[v.id]=v;
                if(v.children&&v.children.length>0){
                    setInfos(v.children);
                }
            })
        };
        return {
            components:{...getThisActionOhterComponents()},
            setup(props,ctx){
                if(self.frameElement && self.frameElement.tagName == "IFRAME"&&self.frameElement.parentElement.classList.contains('arco-modal-body')){
                    document.body.classList.add('arco-modal-iframe-body');
                }
                return getThisActionOhterSetup(props,ctx);
            },
            data(){
                const pagination={
                    pageSize: vueData.indexPageOption.pageSize,
                    showPageSize:vueData.indexPageOption.canGetRequestOption,
                    total:0,
                    current:1,
                }
                return {
                    listColumns:vueData.groupGroupColumns||{'':vueData.listColumns},
                    pagination: {...pagination},
                    loading: false,
                    dataOther:{},
                    data: [],
                    myFilters:{
                        ...pagination,
                        sortField: '',
                        sortOrder: '',
                        page: 1,
                    },
                    showFilter:vueData.showFilter,
                    showTableTool:vueData.showTableTool,
                    canDel:vueData.auth.del,
                    canEdit:vueData.auth.edit,
                    auth:vueData.auth,
                    childs:vueData.childs,
                    filterBase:{
                        filterConfig:vueData.filterConfig,
                        class:vueData.model,
                        name:vueData.modelName,
                        title:vueData.title,
                        filterValues:vueData.filter_data||{},//如果有值，filter-item不显示
                    },
                    showMultipleSelection:typeof vueData.showMultipleSelection==='undefined'?null:vueData.showMultipleSelection,
                    selectedRowKeys:[],
                    rowSelection:{
                        type:'checkbox',
                        showCheckedAll:true,
                    },
                    fieldStepConfig:vueData.fieldStepConfig,
                    actionDefWidth:0,
                    tableThemIsColor:vueData.tableThemIsColor,
                    indexUrl:vueData.listUrl,
                    cWindow:vueData.cWindow||{},
                    showCreateTime:!vueData.showCreateTime,
                    childrenColumnName:vueData.childrenColumnName,
                    indentSize:vueData.indentSize,
                    expandAllRows:vueData.expandAllRows,
                    isTreeIndex:vueData.isTreeIndex,
                    toolTitleLeftBtns:vueData.toolTitleLeftBtns||[],
                    toolTitleRightBtns:vueData.toolTitleRightBtns||[],
                    toolBtnLeftBtns:vueData.toolBtnLeftBtns||[],
                    toolBtnRightBtns:vueData.toolBtnRightBtns||[],
                    leftCate:leftCate,
                    leftCateObj:{
                        sourceData:vueData.leftCate&&vueData.leftCate.list?JSON.parse(JSON.stringify(vueData.leftCate.list)):[],
                        searchValue:'',
                        expandedKeys:vueData.leftCate&&vueData.leftCate.defaultExpandAll?getTreeParentKeys(vueData.leftCate&&vueData.leftCate.list?vueData.leftCate.list:[]):[],
                        selectedKeys:[],
                        loading:false,
                        showTools:leftCateShowTools,
                    },
                    haveFielterShow:true,
                    //其他配置
                    ...getThisActionOhterData(),
                }
            },
            provide(){return getThisActionOhterProvides(this)},
            mounted() {
                this.pageIsInit();
                this.fetch();
            },
            computed:{
                canAdd(){
                    return this.auth.add&&this.auth.stepAdd&&this.auth.rowAuthAdd;
                },
                ...getThisActionOhterComputeds(),
                delSelectedIds(){
                    const ids=[];
                    this.selectedRowKeys.forEach(id=>{
                        if(typeof infos[id]==='undefined'){
                            return;
                        }
                        const record=infos[id];
                        if(!this.$refs.indexcurdtable.isCanDel(record)){
                            return;
                        }
                        if(!record.__auth||typeof record.__auth.del==='undefined'||record.__auth.del===true){
                            ids.push(id)
                        }
                    })
                    return ids;
                }
            },
            watch:{
                'leftCateObj.searchValue'(value){
                    value=value.trim();
                    let newData=JSON.parse(JSON.stringify(this.leftCateObj.sourceData));
                    if(value===''){
                        this.leftCateObj.expandedKeys = getTreeParentKeys(newData);
                        this.leftCate.list=newData;
                    }

                    let expandedKeys=[];
                    let getFinds=function (datas){
                        return datas.filter(function (item){
                            if(item.children){
                                item.children=getFinds(item.children);
                                if(item.children.length>0){
                                    expandedKeys.push(item.value);
                                    return item;
                                }
                            }
                            if(item.title.indexOf(value) > -1){
                                return item;
                            }
                        });
                    }

                    let newTree=getFinds(newData);
                    this.leftCateObj.expandedKeys = expandedKeys;
                    this.leftCate.list=newTree;
                },
                ...getThisActionOhterWatchs(),
            },
            methods:{
                openChildPageBox(option,success){
                    let childIframe = null;
                    this.openBox(option).on('success',function(paramData) {
                        let iframe = typeof paramData.find === 'function' ? layero.find('iframe')[0] : paramData.iframe;
                        if (iframe) {
                            childIframe = iframe;
                        }
                        if(success){
                            success(iframe);
                        }
                    }).on('close',function() {
                        if (childIframe && childIframe.contentWindow.onClose) {
                            childIframe.contentWindow.onClose();
                        }
                    }).end();
                },
                keyValueStr(obj){
                    const arr=[];
                    for(let i in obj){
                        if(typeof obj[i]==='object'){
                            arr.push(this.keyValueStr(obj[i]))
                        }else if(typeof obj[i]==='string'||typeof obj[i]==='number'||(obj[i]&&typeof obj[i].toString!=='undefined')){
                            arr.push(obj[i].toString());
                        }else{
                            arr.push('');
                        }
                    }
                    return arr.join('|');
                },
                pageChange(page){
                    this.pagination.current = page;
                    this.myFilters.page = page;
                    this.fetch();
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
                fetch(resolve) {
                    this.loading = true;
                    const where=this.getWhere();
                    where.pageGuid=VUE_CURD.GUID;
                    where.refreshId=0;
                    let url=this.indexUrl;
                    url=this.getUrlByLeftCate(url);

                    this.$get(url,where).then(data => {
                        this.pagination.current=data.data.current_page;
                        this.pagination.total = data.data.total;
                        this.dataOther=Object.keys(data.data.other).length>0?data.data.other:{};
                        setInfos(data.data.data);
                        this.data = data.data.data;
                        this.loading = false;
                        this.refreshTableTirgger(url,where,data);
                        if(resolve&&typeof resolve==='function'){
                            resolve(url,where,data)
                        }
                    }).catch(()=>{
                        this.loading = false;
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
                refreshTableTirgger(url,where,res){
                    this.onDataLoad(url,where,res);//触发钩子
                    //列表加载完成
                    if(window.onListFetch){
                        Vue.nextTick(()=>{
                            window.onListFetch(this.data);
                        })
                    }
                },
                openAddChildren(row){
                    let url=vueData.defaultUrlTpl.replace('___URL_TPL___','edit');
                    if(vueData.addBtn&&vueData.addBtn.modalUrl){
                        url=vueData.addBtn.modalUrl;
                    }
                    this.openChildPageBox(openParam(row.childAddBtn,'新增 '+vueData.title,url));
                },
                openAdd(){
                    let url=vueData.defaultUrlTpl.replace('___URL_TPL___','edit');
                    if(vueData.addBtn&&vueData.addBtn.modalUrl){
                        url=vueData.addBtn.modalUrl;
                    }
                    url=this.getUrlByLeftCate(url);
                    if(vueData.addBtn&&vueData.addBtn.modalUrl){
                        vueData.addBtn.modalUrl=url;
                    }
                    this.openChildPageBox(openParam(vueData.addBtn,'新增 '+vueData.title,url));
                },
                openEdit(row){
                    if(row.stepInfo&&row.stepInfo.title){
                        const config=getStepOpenConfig(row,row.stepInfo);
                        if(row.stepInfo.config.titleEdit!==''){
                            config.title=row.stepInfo.config.titleEdit;
                        }
                        this.openChildPageBox(config);
                    }else{
                        this.openChildPageBox(openParam(
                            row.editBtn,
                            '修改 '+vueData.title+' 相关信息',
                            setUrlParams(vueData.defaultUrlTpl.replace('___URL_TPL___','edit'),{id:row.id})
                        ));
                    }
                },
                openNext(row){
                    if(row.nextStepInfo&&row.nextStepInfo.listDirectSubmit!==''){
                        getStepJustDo(row,this)
                    }else{
                        this.openChildPageBox(getStepNextOpenConfig(row,'rt'));
                    }
                },
                openShow(row){
                    let url=vueData.defaultUrlTpl.replace('___URL_TPL___','show');
                    if(vueData.showBtn&&vueData.showBtn.modalUrl){
                        url=vueData.showBtn.modalUrl;
                    }
                    url=this.getUrlByLeftCate(url);
                    if(vueData.showBtn&&vueData.showBtn.modalUrl){
                        vueData.showBtn.modalUrl=url;
                    }
                    this.openChildPageBox(openParam(
                        row.showBtn,
                        '查看 '+vueData.title+' 相关信息',
                        setUrlParams(url,{id:row.id})
                    ));
                },
                delSelectedRows(e,delChilds){
                    this.loading = true;
                    let url=vueData.delUrl;
                    let where={ids:this.delSelectedIds,delChilds:delChilds?1:0};
                    this.$post(url,where).then(res=>{
                        ArcoVue.Message.success(res.msg);
                        this.refreshTable();
                        this.selectedRowKeys=[];
                        if(typeof window.onDel==='function'){
                            window.onDel(url,where,res);
                        }
                    }).catch(err=>{
                        this.loading = false;
                        if(!delChilds&&vueData.deleteHaveChildErrorCode&&err.errorCode==vueData.deleteHaveChildErrorCode){
                            ArcoVue.Message.clear();
                            if(top.ArcoVue){
                                top.ArcoVue.Message.clear();
                            }
                            let childsText='';
                            if(vueData.childs){
                                childsText='（'+vueData.childs.map(v=>v.title).join('、')+'）';
                            }
                            (top.ArcoVue||ArcoVue).Modal.confirm({
                                content: '已有子数据，将删除下面所有子数据。确定删除所选数据及下面所有子数据'+childsText+'？',
                                okText: '确定删除',
                                cancelText: '取消',
                                onOk:()=> {
                                    this.delSelectedRows(e,true)
                                },
                            })
                        }
                    })
                },
                deleteRow(row,delChilds){
                    this.loading = true;
                    let url=vueData.delUrl;
                    let where={ids:[row.id],delChilds:delChilds?1:0};
                    this.$post(url,where).then(res=>{
                        ArcoVue.Message.success(res.msg);
                        this.refreshTable();
                        if(typeof window.onDel==='function'){
                            window.onDel(url,where,res);
                        }
                    }).catch(err=>{
                        this.loading = false;
                        if(!delChilds&&vueData.deleteHaveChildErrorCode&&err.errorCode==vueData.deleteHaveChildErrorCode){
                            ArcoVue.Message.clear();
                            if(top.ArcoVue){
                                top.ArcoVue.Message.clear();
                            }
                            let childsText='';
                            if(vueData.childs){
                                childsText='（'+vueData.childs.map(v=>v.title).join('、')+'）';
                            }
                            const modal = (top.ArcoVue||ArcoVue).Modal.confirm({
                                content:warnIconContent('已有子数据，将删除下面所有子数据。确定删除本条数据及下面所有子数据'+childsText+'？'),
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
                    this.fetch(function (url,where,data){
                        if(typeof window.onRefreshTable==='function'){
                            window.onRefreshTable(url,where,data);
                        }
                    });
                },
                refreshId(id){
                    this.loading = true;
                    const where=this.getWhere();
                    where.id=id;
                    where.page=1;
                    // where.pageGuid=VUE_CURD.GUID;
                    where.refreshId=1;
                    let url=this.indexUrl;
                    this.$get(url,where).then(data => {
                        if(typeof window.onRefreshId==='function'){
                            window.onRefreshId(url,where,data);
                        }

                        if(!data.data.data[0]){
                            this.loading = false;
                            return;
                        }
                        //为了触发watch
                        let isChange=false;
                        const getRows=function (list){
                            const rows=[];
                            for(let i in list){
                                let row=list[i];
                                if(row.id==id){
                                    const children=row.children;
                                    row=data.data.data[0];
                                    if(children){
                                        row.children=children;
                                    }
                                    isChange=true;
                                }else if(row.children&&Array.isArray(row.children)&&row.children.length>0){
                                    row.children=getRows(row.children)
                                }
                                rows.push(row);
                            }
                            return rows;
                        }
                        const rows=getRows(this.data);
                        if(isChange===false){
                            this.loading = false;
                            return;
                        }
                        this.data=rows;
                        //如果有才改变
                        for(const n in data.data.other){
                            this.dataOther[n]=data.data.other[n]
                        }
                        data.data.data.forEach(item=>{
                            infos[item.id]=item;
                        })
                        this.loading = false;
                        this.refreshTableTirgger(this.indexUrl,where,data);
                    }).catch(()=>{
                        this.loading = false;
                    });
                },
                doFilter(){
                    this.pagination.current = 1;
                    this.myFilters.page=1;
                    this.fetch((url,where,data)=>{
                        this.selectedRowKeys=[];
                    })
                },
                openChildList(row,modelInfo,btn){
                    this.openChildPageBox({
                        title:modelInfo.title,
                        offset:btn.pageOffset,
                        content: btn.url,
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
                            ArcoVue.Message.success(res.msg);
                            this.refreshTable()
                        }
                    }).trigger();
                },
                exportData(){
                    const url=setUrlParams(vueData.exportUrl,{pageGuid:VUE_CURD.GUID})
                    if(this.pagination.total<10000){
                        window.open(url);
                        return;
                    }
                    //如果数据大于1万条，提出警告
                    (top.ArcoVue||ArcoVue).Modal.confirm({
                        title: warnIconContent('导出提示','#faad14',true),
                        content: '当前结果数据较多，可能会发生不能正确导出数据的情况，建议筛选后再导出',
                        okText: '仍然导出',
                        cancelText: '取消导出',
                        onOk() {
                            window.open(url);
                        }
                    });
                },
                actionWidth(row){
                    return this.actionDefWidth
                },
                onDataLoad(url,where,res){
                    //数据获取完成钩子
                },
                getInfos(){
                    return infos;
                },
                leftCateExpand(){
                    //展开左侧分组
                    this.leftCateObj.expandedKeys=getTreeParentKeys(this.leftCate.list||[])
                },
                leftCateShrink(){
                    //收起左侧分组
                    this.leftCateObj.expandedKeys=[];
                },
                leftCateSelect(selectedKeys,e){
                    this.leftCateObj.selectedKeys=selectedKeys;
                    this.$refs['filter'].restFilter();
                    this.doFilter();
                },
                leftCateRefresh(){
                    this.leftCateObj.loading=true;
                    this.$get(this.indexUrl,{'get_left_cate':1}).then(data => {
                        this.leftCate=data.data;
                        setLCST(this.leftCate.list);
                        this.leftCateObj.showTools=leftCateShowTools;
                        this.leftCateObj.sourceData= JSON.parse(JSON.stringify(data.data.list));
                        this.leftCateObj.searchValue='';
                        this.leftCateObj.loading = false;
                    }).catch(()=>{
                        this.leftCateObj.loading = false;
                    });

                },
                titleByLeftCateSelect(title){
                    if(!this.leftCate.show||!this.leftCateObj.selectedKeys||this.leftCateObj.selectedKeys.length===0){
                        return title;
                    }
                    let cate=this.getTitleByLeftCateVal(this.leftCateObj.selectedKeys[0],this.leftCateObj.sourceData);
                    if(cate===''){
                        return title;
                    }
                    return cate+'：'+title;
                },
                getTitleByLeftCateVal(val,tree){
                    for(let i in tree){
                        if(tree[i].value===val){
                            return tree[i].title;
                        }else if(tree[i].children){
                            let title=this.getTitleByLeftCateVal(val,tree[i].children);
                            if(title!==''){
                                return title
                            }
                        }
                    }
                    return '';
                },
                getUrlByLeftCate(url){
                    if(!this.leftCate||!this.leftCate.show){
                        return url;
                    }
                    const leftCateVal=this.leftCateObj.selectedKeys[0]||0;

                    if(url.indexOf('&'+this.leftCate.paramName+'=')>-1){
                        url=url.replace(new RegExp('\\&'+this.leftCate.paramName+'=\\d*'),'&'+this.leftCate.paramName+'='+leftCateVal)
                    }else if(url.indexOf('?'+this.leftCate.paramName+'=')>-1){
                        url=url.replace(new RegExp('\\?'+this.leftCate.paramName+'=\\d*'),'?'+this.leftCate.paramName+'='+leftCateVal)
                    }else{
                        url=setUrlParams(url,{[this.leftCate.paramName]:leftCateVal})
                    }
                    return url;
                },
                leftCateOpenAdd(){
                    if(!this.leftCate.addBtn.modalUrl||this.leftCate.addBtn.modalUrl.trim()===''){
                        return;
                    }
                    this.openChildPageBox(openParam(this.leftCate.addBtn,'新增 '+this.leftCate.title,this.leftCate.addBtn.modalUrl),iframe=>{
                        iframe.contentWindow.listVue={refreshTable:this.leftCateRefresh,}
                    });
                },
                leftCateOpenEdit(row){
                    if(!this.leftCate.editBtn.modalUrl||this.leftCate.editBtn.modalUrl.trim()===''){
                        return;
                    }
                    this.leftCateObj.showTools[row.value]=false;
                    this.leftCate.editBtn.modalUrl=setUrlParams(this.leftCate.editBtn.modalUrl,{id:row.value});
                    this.openChildPageBox(openParam(this.leftCate.editBtn,'修改 '+this.leftCate.title,this.leftCate.editBtn.modalUrl),iframe=>{
                        iframe.contentWindow.listVue={refreshTable:this.leftCateRefresh,refreshId:this.leftCateRefresh}
                    });
                },
                leftCateDeleteRow(row,delChilds){
                    if(!this.leftCate.rmUrl||this.leftCate.rmUrl.trim()===''){
                        return;
                    }
                    this.leftCateObj.showTools[row.value]=false;
                    this.leftCateObj.loading=true;
                    this.$post(this.leftCate.rmUrl,{ids:[row.value],id:row.value,delChilds:delChilds?1:0}).then(res=>{
                        ArcoVue.Message.success(res.msg);
                        this.leftCateRefresh();
                    }).catch(err=>{
                        this.leftCateObj.loading = false;
                        if(!delChilds&&vueData.deleteHaveChildErrorCode&&err.errorCode==vueData.deleteHaveChildErrorCode){
                            ArcoVue.Message.error('当前项已有关联数据，不可删除');
                        }
                    })
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
            components:{...getThisActionOhterComponents()},
            setup(props,ctx){
                return getThisActionOhterSetup(props,ctx);
            },
            data() {
                return {
                    loading:false,
                    haveGroup:!!vueData.groupFields,
                    groupFields:vueData.groupFields||{'':vueData.fields},
                    groupGrids:vueData.groupGrids||{},
                    labelCol: { span: 4 },
                    wrapperCol: { span: 18 },
                    form:form,
                    info:vueData.info||{},
                    fieldHideList:{},
                    ...getThisActionOhterData(),
                }
            },
            provide(){return getThisActionOhterProvides(this)},
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
                },
                ...getThisActionOhterComputeds(),
            },
            watch:{
                ...getThisActionOhterWatchs(),
            },
            methods:{
                onSubmit(option){
                    if(window.vueEditOnSubmit){
                        return  window.vueEditOnSubmit.bind(this)(option);
                    }
                    //我想要子组件可以不关闭当前窗口提交（就是自定义的字段可以新增数据后继续编辑）
                    option=option||{};

                    this.$refs.pubForm.validate(async errors =>{
                        if(errors&&Object.keys(errors).length>0){
                            this.$message.warning(errors[Object.keys(errors)[0]].message);
                            return;
                        }

                        for(let i in this.groupFields){
                            if(this.$refs['fieldGroup'+i]&&this.$refs['fieldGroup'+i].validateListForm){
                                if(await this.$refs['fieldGroup'+i].validateListForm()===false){
                                    return;
                                }
                            }
                        }
                        if(option.showLoading!==false){
                            this.loading=true;
                        }
                        this.$post(window.location.href,this.form).then(async res=>{
                            const message = top.ArcoVue || (parentWindow || window).ArcoVue;
                            if(message){
                                message.Message.success(res.msg);
                            }

                            if(this.form&&this.form.id&&!res.data.refreshList){
                                window.listVue.refreshId(this.form.id);
                            }else{
                                window.listVue.refreshTable();
                            }
                            if(typeof window.onSubmit==='function'){
                                window.onSubmit(window.location.href,this.form,res);
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
                    })

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
                ...getThisActionOhterMethods()
            }
        }
    };


    actions.show=function(){
        return {
            components:{...getThisActionOhterComponents()},
            setup(props,ctx){
                return getThisActionOhterSetup(props,ctx);
            },
            data(){
                return {
                    info:vueData.info,
                    haveGroup:vueData.groupFields?true:false,
                    groupFields:vueData.groupFields||{'':vueData.fields},
                    groupGrids:vueData.groupGrids||{},
                    fieldComponents,
                    //其他配置
                    ...getThisActionOhterData(),
                }
            },
            provide(){return getThisActionOhterProvides(this)},
            computed:{
                ...getThisActionOhterComputeds(),
            },
            watch:{
                ...getThisActionOhterWatchs(),
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
                gridStyle(title){
                    const style={};
                    if(!this.groupGrids[title]){
                        return style;
                    }

                    for(let i in this.groupGrids[title]){
                        if(this.groupGrids[title][i]){
                            style[i]=this.groupGrids[title][i];
                        }
                    }
                    if(Object.keys(style).length>0){
                        style.display='grid';
                    }
                    return style;
                },
                fieldStyle(field,groupTitle){
                    const style={};
                    if(!field.grid||!this.groupGrids[groupTitle]){
                        return style;
                    }
                    for(let i in field.grid){
                        if(field.grid[i]){
                            style[i]=field.grid[i];
                        }
                    }
                    return style;
                },
                ////其他配置
                ...getThisActionOhterMethods()
            }

        }
    }

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