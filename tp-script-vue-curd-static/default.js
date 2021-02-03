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
                        title:'查看 '+vueData.title,
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
        let form={},fileList={},dateDefaultValues={},validateStatus={},fieldObjs={};

        function fieldInit(field,formData,changeFormDataByInfo){
            if(changeFormDataByInfo){
                formData[field.name]=vueData.info?vueData.info[field.name]:'';
            }
            fieldObjs[field.name]=field;
            switch (field.type){
                case 'ImagesField':
                    if(formData[field.name]){
                        let imgList=typeof formData[field.name]==='string'?formData[field.name].split('|'):formData[field.name],fid=0;
                        fileList[field.name]=imgList.map(function(v){
                            fid--;
                            return {
                                uid:fid,
                                name:v.substring(v.lastIndexOf("/")+1,v.length),
                                status: 'done',
                                url:v,
                            };
                        })
                    }else{
                        fileList[field.name]=[];
                    }
                    break;
                case 'DateField':
                    if(formData[field.name]){
                        if(/^\-?\d+$/g.test(formData[field.name].toString())){
                            //时间戳
                            dateDefaultValues[field.name]=parseTime(formData[field.name],'{y}-{m}-{d}');
                            formData[field.name]=dateDefaultValues[field.name];
                        }else{
                            dateDefaultValues[field.name]=formData[field.name];
                        }
                    }else{
                        dateDefaultValues[field.name]='';
                    }
                    break;
                case 'MonthField':
                    if(formData[field.name]){
                        if(/^\d+$/g.test(formData[field.name].toString())){
                            //时间戳
                            formData[field.name]=parseTime(formData[field.name],'{y}-{m}');
                        }
                    }
                    break;
                case 'WeekField':
                    if(formData[field.name]){
                        if(/^\d+$/g.test(formData[field.name].toString())){
                            //时间戳
                            formData[field.name]=parseTime(formData[field.name],'{y}-{m}-{d}');
                        }
                    }
                    break;
                case 'SelectField':
                    if(field.multiple){
                        formData[field.name]=formData[field.name]?formData[field.name].split(','):[];
                    }
                    break;
                case 'CheckboxField':
                    formData[field.name]=formData[field.name]?formData[field.name].split(','):[];
                    break;
                case 'RegionField':
                    if(!field.readOnly&&field.editShow===true&&field.required===true&&!formData.id&&!formData[field.name]){
                        //如果是添加，且是必填，且为空
                        if(field.regionTree.length===1){
                            if(!field.regionTree[0].children||field.regionTree[0].children.length===0){
                                formData[field.name]=[field.regionTree[0].id];
                            }else if(field.regionTree[0].children.length===1){
                                formData[field.name]=[field.regionTree[0].id,field.regionTree[0].children[0].id];
                            }
                        }
                    }
                    break;
                case 'YearMonthField':
                    if(formData[field.name]){
                        formData[field.name]=[Math.floor(formData[field.name]/12),formData[field.name]%12];
                    }else{
                        formData[field.name]=['',''];
                    }
                    break;
                case 'ListField':
                    let listFieldObjs={};
                    if(formData[field.name]){
                        let lists=JSON.parse(formData[field.name]);
                        for(let n in lists){
                            listFieldObjs[window.guid()]=initListField(field,lists[n]);
                        }
                    }else{
                        listFieldObjs[window.guid()]=initListField(field);
                    }
                    formData[field.name]=listFieldObjs;
                    break;
            }

            return formData
        }

        function initListField(field,data){
            data=data||{};
            field.fields.forEach(function(f){
                data=fieldInit(f,data);
            })
            return data;
        }


        vueData.fields.forEach(function(field){
            form=fieldInit(field,form,true);
        })


        if(vueData.info){
            form.id=vueData.info.id;
        }




        /**
         * 值是否在数组中。将转换为字符串判断（arr 可以是字符串，会转换为数组）
         * @param arr
         * @param val
         * @returns {boolean}
         */
        function arrHave(arr,val){
            if(typeof arr==='string'){
                arr=arr?[]:arr.split(',')
            }
            let have=false;
            arr.some(selected=>{
                if(selected.toString()===val.toString()){
                    have=true;
                    return true;
                }
            })
            return have;
        }

        return {
            components: {
                'field-group-item': {
                    data(){
                        return {
                            fieldObjs:fieldObjs,
                            fileList:fileList,
                            dateDefaultValues:dateDefaultValues,
                            validateStatus:validateStatus,
                            triggerShowss:{},
                            autoCompleteOptions:{},
                        }
                    },
                    name:'fieldGroupItem',
                    props:['groupFieldItems','form','listFieldLabelCol','listFieldWrapperCol'],
                    watch:{
                        form:{
                            handler(form){
                                this.groupFieldItems.forEach(field=>{
                                    if(field.items&&field.items.length>0){
                                        field.items.map(item=>{
                                            if(item.hideFields&&item.hideFields.length>0){
                                                item.hideFields.map(hideField=>{
                                                    this.triggerShowss[hideField.name]=this.triggerShowss[hideField.name]||{};
                                                    switch (field.type){
                                                        case 'CheckboxField':
                                                            this.triggerShowss[hideField.name][field.name]=arrHave(form[field.name],item.value);
                                                            break;
                                                        case 'SelectField':
                                                            if(field.multiple){
                                                                this.triggerShowss[hideField.name][field.name]=arrHave(form[field.name],item.value);
                                                            }else{
                                                                this.triggerShowss[hideField.name][field.name]=form[field.name]===item.value
                                                            }
                                                            break;
                                                        default:
                                                            this.triggerShowss[hideField.name][field.name]=form[field.name]===item.value
                                                    }
                                                })
                                            }
                                        })
                                    }
                                })
                            },
                            immediate:true,
                            deep: true,
                        }
                    },
                    methods:{
                        ...vueDefMethods,
                        handleRemove(name){
                            return file => {
                                if(file.url){
                                    this.form[name]=this.form[name].split('|').filter(url=>url&&url!==file.url).join('|');
                                }
                            }
                        },
                        handlePreview(file,name) {
                            const images=this.fileList[name].filter(function(vo){
                                return vo.url?true:false;
                            }).map(function(vo){
                                return vo.url
                            });
                            window.top.showImages(images,images.indexOf(file.url))
                        },
                        handleChange(data,name) {
                            let urls=[];
                            this.fileList[name] =data.fileList.map(function(file){
                                if(file.status==='done'){
                                    if(file.response){
                                        if(file.response.code==0){
                                            antd.message.error('文件[ '+file.name+' ]：'+file.response.msg,6);
                                        }else{
                                            file.url=file.response.data.url
                                        }
                                    }
                                }
                                return file;
                            }).filter(function(file){
                                if(file.status==='done'){
                                    if(urls.indexOf(file.url)!==-1||!file.url){
                                        return false;
                                    }
                                    urls.push(file.url)
                                }
                                return true;
                            });
                            this.form[name]=urls.join('|');
                            if(this.form[name]){
                                this.validateStatus[name]='success'
                            }else{
                                this.validateStatus[name]='error'
                            }
                        },
                        fieldRules(field){
                            return {
                                required:this.triggerShows(field.name)&&field.required,
                                message:field.title+' ， 必填',
                            }
                        },
                        dateChange(date,name){
                            this.form[name]=date.format('YYYY-MM-DD');
                        },
                        onRegionChange(event,name){
                            this.validateStatus[name]='success'
                        },
                        selectDefValue(field){
                            if(field.multiple&&typeof this.form[field.name]==='string'){
                                return this.form[field.name]?this.form[field.name].split(','):[];
                            }
                            return this.form[field.name];
                        },
                        onRadioChange(e,field){
                        },
                        onCheckboxChange(e,field){
                        },
                        triggerShows(fieldName){
                            if(!this.triggerShowss[fieldName]){
                                return true;
                            }
                            for(let k in this.triggerShowss[fieldName]){
                                if(this.triggerShowss[fieldName][k]===true){
                                    return true;
                                }
                            }
                            return false;
                        },
                        moreStringFieldIsArr(field){
                            if(typeof this.form[field.name]!=='object'){
                                let data={};
                                if(this.form[field.name]){
                                    this.form[field.name].split(field.separate).forEach(v=>{
                                        data[window.guid()]=v;
                                    })
                                }else{
                                    data[window.guid()]='';
                                }
                                this.form[field.name]=data;
                            }
                            return true;
                        },
                        addMoreString(field){
                            this.moreStringFieldIsArr(field);
                            this.form[field.name][window.guid()]='';
                        },
                        removeMoreString(field,key){
                            this.moreStringFieldIsArr(field);
                            delete this.form[field.name][key];
                        },
                        addListField(field){
                            this.form[field.name][window.guid()]=initListField(field);
                        },
                        removeListField(field,key){
                            delete this.form[field.name][key];
                        },
                        async validateListForm() {
                            //外部调用
                            let isNotErr = true;
                            for (const field of this.groupFieldItems) {
                                if (isNotErr&&field.type === 'ListField') {
                                    for (let i in this.form[field.name]){
                                        if (this.$refs['listFieldForm' + i]) {
                                            isNotErr=await new Promise(resolve => {
                                                this.$refs['listFieldForm' + i].validate().then(res=>{
                                                    resolve(true);
                                                }).catch(error => {
                                                    if (error.errorFields && error.errorFields[0] && error.errorFields[0].errors && error.errorFields[0].errors[0]) {
                                                        antd.message.warning(field.title + ':' + error.errorFields[0].errors[0])
                                                    } else {
                                                        antd.message.warning('请检测' + field.title + '是否填写正确')
                                                    }
                                                    console.log('error', error);
                                                    resolve(false);
                                                });
                                            })
                                        }
                                    }

                                }
                            }
                            return isNotErr;
                        },
                        changePwdType(field){

                        },
                        autoCompleteSearch(val,url,key){
                            this.autoCompleteOptions[key]=[];
                            if(!url){
                                return ;
                            }

                            this.$get(url,{search:val}).then(res=>{
                                let arr=[];
                                res.data.forEach(function(v){
                                    arr.push({value:v});
                                })
                                this.autoCompleteOptions[key]=arr;
                            })
                        },
                        onAutoCompleteSearch(event,field){
                            this.autoCompleteSearch(event,field.url,field.name)
                        },

                        onAutoCompleteSearchMoreString(event,field,key){
                            this.autoCompleteSearch(event,field.url,field.name+'.'+key)
                        }
                    },
                    template:`
                        <div>
                            <div v-for="field in groupFieldItems">
                                <transition name="slide-fade">
                                <a-form-item v-if="field.editShow" v-show="triggerShows(field.name)" :label="field.title" :name="field.name" :rules="fieldRules(field)" :validate-status="validateStatus[field.name]" class="form-item-row">
                                    <div v-if="field.type==='StringField'">
                                        <a-input v-model:value="form[field.name]" :placeholder="field.placeholder||'请填写'+field.title" :suffix="field.ext" :disabled="field.readOnly"/>
                                    </div>
                                    <div v-if="field.type==='StringAutoCompleteField'" class="field-box">
                                        <div class="l">
                                            <a-auto-complete v-model:value="form[field.name]" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly" :options="autoCompleteOptions[field.name]" @search="onAutoCompleteSearch($event,field)"/>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-if="field.type==='TextField'" class="field-box">
                                        <div class="l">
                                            <a-textarea v-model:value="form[field.name]" :auto-size="{ minRows: 2, maxRows: 5 }"
                                                        :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly"/>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-if="field.type==='IntField'" class="field-box">
                                        <div class="l">
                                            <a-input-number v-model:value="form[field.name]" :min="field.min" :max="field.max"
                                                            :placeholder="field.placeholder||'输入整数'" :disabled="field.readOnly" style="width: 100%;"/>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-if="field.type==='DecimalField'" class="field-box">
                                        <div class="l">
                                            <a-input-number v-model:value="form[field.name]"
                                                            :min="field.min"
                                                            :max="field.max"
                                                            :placeholder="field.placeholder||(field.precision?'保留'+field.precision+'位小数':'填入整数')"
                                                            :disabled="field.readOnly"
                                                            @change="value => form[field.name]=value?parseFloat(Number(value).toFixed(field.precision)):''"
                                                            style="width: 100%;"/>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='DateField'" class="field-box">
                                        <div class="l">
                                            <a-date-picker
                                                v-model:value="dateDefaultValues[field.name]"
                                                type="date"
                                                :placeholder="field.placeholder||'请选择日期'"
                                                 :disabled="field.readOnly"
                                                style="width: 100%;"
                                                @change="dateChange($event,field.name)"
                                            />
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='ImagesField'" class="field-box">
                                        <div class="l">
                                            <a-upload
                                                multiple
                                                action="/admin/ajax/upload"
                                                accept="image/*"
                                                list-type="picture-card"
                                                :file-list="fileList[field.name]"
                                                :remove="handleRemove(field.name)"
                                                 :disabled="field.readOnly"
                                                @preview="handlePreview($event,field.name)"
                                                @change="handleChange($event,field.name)"
                                            >
                                                <plus-outlined />
                                            </a-upload>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='RegionField'" class="field-box">
                                        <template v-if="form.id">
                                            <div class="l">
                                                {{form[field.pField]}}/{{form[[field.cField]]}}
                                            </div>
                                        </template>
                                        <template v-else>
                                            <div class="l">
                                                <a-cascader
                                                    v-model:value="form[field.name]"
                                                    :options="field.regionTree"
                                                    :placeholder="field.placeholder||'请选择村社'"
                                                    show-search
                                                     :disabled="field.readOnly"
                                                    @change="onRegionChange($event,field.name)"
                                                />
                                            </div>
                                            <div class="r">
                                                <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                            </div>
                                        </template>
                                    </div>
                                    <div v-else-if="field.type==='SelectField'" class="field-box">
                                        <div class="l">
                                            <a-select :mode="field.multiple?'multiple':'default'"
                                                      :default-value="selectDefValue(field)"
                                                      v-model:value="form[field.name]"
                                                      :placeholder="field.placeholder||'请选择'+field.title"
                                                       :disabled="field.readOnly"
                                                      show-search>
                                                <a-select-option :value="optionItem.value" v-for="optionItem in field.items">
                                                    {{optionItem.text}}
                                                </a-select-option>
                                            </a-select>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='RadioField'" class="field-box">
                                        <div class="l">
                                            <a-radio-group v-model:value="form[field.name]" @change="onRadioChange($event,field)"
                                             :disabled="field.readOnly">
                                                <a-radio :value="radioItem.value"  v-for="radioItem in field.items">
                                                    {{radioItem.text}}
                                                </a-radio>
                                            </a-radio-group>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='CheckboxField'" class="field-box">
                                        <div class="l">
                                            <a-checkbox-group v-model:value="form[field.name]"  :disabled="field.readOnly">
                                                <a-checkbox :value="checkboxItem.value"  v-for="checkboxItem in field.items" @change="onCheckboxChange($event,field)">
                                                    {{checkboxItem.text}}
                                                </a-checkbox>
                                            </a-checkbox-group>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='WeekField'" class="field-box">
                                        <div class="l">
                                            <week-select v-model:value="form[field.name]" :placeholder="field.placeholder" :disabled="field.readOnly"></week-select>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='MonthField'" class="field-box">
                                        <div class="l">
                                            <a-month-picker v-model:value="form[field.name]" :placeholder="field.placeholder||'请选择月份'" 
                                            value-format="YYYY-MM" 
                                            :disabled="field.readOnly" style="width: 100%"/>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='MapRangeField'" class="field-box">
                                        <div class="l">
                                           <map-range v-model:value="form[field.name]" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择区域'" ></map-range>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='MoreStringField'">
                                        <div class="inputs-box">
                                          <transition-group name="slide-fade">
                                            <div class="inputs-box-item" v-for="(item,key) in form[field.name]" :key="key">
                                                <template v-if="field.url">
                                                    <div class="field-box">
                                                        <div class="l">
                                                            <div class="more-string-auto-complete-row">
                                                                <div class="more-string-auto-complete-input">
                                                                    <a-auto-complete v-model:value="form[field.name][key]" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly" :options="autoCompleteOptions[field.name+'.'+key]" @search="onAutoCompleteSearchMoreString($event,field)"/>
                                                                </div>
                                                                <div class="more-string-auto-complete-rm"><close-outlined class="remove-inputs-box-item-icon" @click="removeMoreString(field,key)"></close-outlined></div>
                                                            </div>
                                                        </div>
                                                        <div class="r">
                                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                                        </div>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                    <a-input v-model:value="form[field.name][key]" :placeholder="field.placeholder||'请填写'+field.title" :suffix="field.ext" :disabled="field.readOnly">
                                                    <template v-if="!field.readOnly" #addon-after><close-outlined class="remove-inputs-box-item-icon" @click="removeMoreString(field,key)"></close-outlined></template>
                                                    </a-input>
                                                </template>
                                                
                                            </div>
                                            </transition-group>
                                        </div>
                                        <div class="inputs-add-btn-box" v-if="!field.readOnly">
                                             <plus-outlined class="add-inputs-box-item-icon" @click="addMoreString(field)"></plus-outlined>                                        
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='YearMonthField'" class="field-box">
                                        <div class="l year-month-field-box">
                                            <a-input-group compact>
                                                <a-input-number v-model:value="form[field.name][0]" min="0" max="999" placeholder="输入年数" :disabled="field.readOnly"/>
                                                <a-input value="年" :disabled="true"/>
                                                <a-input-number v-model:value="form[field.name][1]" min="0" max="12" placeholder="输入月数" :disabled="field.readOnly"/>
                                                <a-input value="个月" :disabled="true"/>
                                            </a-input-group>
                                        </div>
                                        <div class="r">
                                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="field.type==='ListField'" >
                                        <div class="list-field-box">
                                            <transition-group name="slide-fade">
                                                <div class="list-field-box-item-box" v-for="(item,key) in form[field.name]" :key="key">
                                                    <a-divider class="list-field-box-item-divider" dashed></a-divider>
                                                     <a-form class="list-field-box-item-form" :model="item" :label-col="listFieldLabelCol" :wrapper-col="listFieldWrapperCol" :ref="'listFieldForm'+key">
                                                       <div class="list-field-box-remove"><close-outlined class="remove-list-field-box-item-icon" @click="removeListField(field,key)"></close-outlined></div>
                                                       <field-group-item class="list-field-box-item" :group-field-items="field.fields" v-model:form="item"></field-group-item>
                                                     </a-form>
                                                </div>
                                            </transition-group>
                                        </div>
                                        <div class="list-field-add-btn-box" v-if="!field.readOnly">
                                             <plus-outlined class="add-list-field-box-item-icon" @click="addListField(field)"></plus-outlined>                                        
                                        </div>
                                    </div>
                                   <div v-if="field.type==='PasswordField'" class="field-box">
                                    <div class="l">
                                        <input type="text" 说明="不填充密码" style="height: 1px;width:1px;padding: 0;border: 0;opacity: 0.01;position: absolute">
                                        <input type="password" 说明="不填充密码" style="height: 1px;width:1px;padding: 0;border: 0;opacity: 0.01;position: absolute">
                                        <a-input-password v-model:value="form[field.name]" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly"></a-input-password>
                                    </div>
                                    <div class="r"><span v-if="field.ext" class="ext-span">{{ field.ext }}</span></div>
                                    </div>
                                </a-form-item>
                                </transition>
                            </div>
                        </div>
                    `,
                }
            },
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
            dataObj.form.base_id=vueData.baseId;
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