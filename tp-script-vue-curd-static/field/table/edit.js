define([], function () {
    const styleId='table-field-edit-field-style';
    const style = `
<style id="${styleId}">
.table-field-box-add+.table-field-box-table{
padding-top: 12px;
}
.table-field-show-modal{
width: 95%;
max-width: 720px;
max-height: 92vh;
}
.table-field-show-modal .arco-modal-body{
max-height: calc(92vh - 170px);
}
.table-field-box-table .arco-table:not(.arco-table-border-header-cell) .arco-table-element>thead>.arco-table-tr:first-child>.arco-table-th:first-child{
    border-top-left-radius: 6px;
}
.table-field-box-table .arco-table:not(.arco-table-border-header-cell) .arco-table-element>thead>.arco-table-tr:last-child>.arco-table-th:first-child{
    border-bottom-left-radius: 6px;
}
.table-field-box-table .arco-table:not(.arco-table-border-header-cell) .arco-table-element>thead>.arco-table-tr:first-child>.arco-table-th:last-child{
    border-top-right-radius: 6px;
}
.table-field-box-table .arco-table:not(.arco-table-border-header-cell) .arco-table-element>thead>.arco-table-tr:last-child>.arco-table-th:last-child{
    border-bottom-right-radius: 6px;
}
</style>
`;
    return {
        props: ['field', 'value', 'validateStatus', 'listFieldLabelCol', 'listFieldWrapperCol', 'groupFieldItems','fieldHideList','info','disabled'],
        setup:function (props,ctx){
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }

            window.fieldComponents=window.fieldComponents||{};
            const componentUrl=props.field.pageData.componentUrl||{};
            const addComponents={};
            for(let i in componentUrl){
                if(!window.fieldComponents[i]&&componentUrl[i].jsUrl){
                    window.fieldComponents[componentUrl[i].name]=componentUrl[i].jsUrl;
                    addComponents[componentUrl[i].name]=componentUrl[i].jsUrl;
                }
            }
            const addComponentVals=Object.values(addComponents);0
            const isInit=Vue.ref(false);
            if(addComponentVals.length>0){
                require(addComponentVals,()=>{
                    for(let componentName in addComponents){
                        window.app.component(componentName,typeof require(fieldComponents[componentName])==='function'?require(fieldComponents[componentName])():require(fieldComponents[componentName]))
                    }
                    isInit.value=true;
                });
            }else{
                isInit.value=true;
            }
            let addMaxId=Vue.ref(0);
            let list=Vue.ref(null);
            Vue.watch(()=>props.info, (newValue, oldValue) => {
                    list.value = newValue[props.field.name+'List']||newValue[props.field.name+'Arr']||null;
                    if(list.value===null){
                        list.value=newValue[props.field.name];
                    }
                    if(typeof list.value==='string'){
                        list.value=JSON.parse(list.value);
                    }
                    if(!list.value){
                        list.value=[];
                    }
                    for(let i in list.value){
                        if(!list.value[i].id){
                            addMaxId.value++;
                            list.value[i].id=addMaxId.value;
                        }else if(addMaxId.value<list.value[i].id){
                            addMaxId.value=list.value[i].id;
                        }
                    }
                },
                { immediate: true }
            )

            Vue.watch(()=>props.value, (newValue, oldValue) => {
                if(newValue=== JSON.stringify(list.value)){
                    return;
                }
                list.value=JSON.parse(newValue||'[]');
            });

            const editData=Vue.ref({
                haveGroup:!!props.field.pageData.editGroupFields,
                groupFields:props.field.pageData.editGroupFields||{'':props.field.fields},
                fieldHideList:{},
            });
            const showData=Vue.ref({
                haveGroup:!!props.field.pageData.showGroupFields,
                groupFields:props.field.pageData.showGroupFields||{'':props.field.pageData.showFields},
            });

            Vue.watch(()=>[props.field.pageData.editGroupFields,props.field.fields], ([editGroupFields,fields]) => {
                editData.value.haveGroup=!!editGroupFields;
                editData.value.groupFields=editGroupFields||{'':fields};
            });

            Vue.watch(()=>[props.field.pageData.showGroupFields,props.field.pageData.showFields], ([showGroupFields,showFields]) => {
                showData.value.haveGroup=!!showGroupFields;
                showData.value.groupFields=showGroupFields||{'':showFields};
            });

            return {
                list:list,
                tableLoading:Vue.ref(false),
                showLook:Vue.ref(false),
                editShow:Vue.ref(false),
                canEdit:true,
                canDel:true,
                actionDefWidth:Vue.ref(0),
                editForm:Vue.ref({}),
                editInfo:Vue.ref({}),
                editLabelCol: Vue.ref({ span: 4 }),
                editWrapperCol: Vue.ref({ span: 18 }),
                editData:editData,
                showData:showData,
                showInfo:Vue.ref({}),
                addMaxId: addMaxId,
                fieldComponents,
                isInit,
            }
        },
        computed:{
            listColumns(){
                const returnData=this.field.pageData.editGroupColumns||{'':this.field.pageData.listColumns};
                for(let i in returnData){
                    for (let n in returnData[i]){
                        if(returnData[i][n].listSort){
                            returnData[i][n].listSort=(a, b) => a[returnData[i][n].name] - b[returnData[i][n].name];
                        }
                    }
                }

                return returnData
            },
            editShowGroup(){
                if(!this.editData.haveGroup){
                    return false;
                }
                let showGroupNum=0;
                for(let i in this.editData.groupFields){
                    if(this.checkShowGroup(this.editData.groupFields[i])){
                        if(showGroupNum>0){
                            return true;
                        }
                        showGroupNum++;
                    }
                }
                return false;
            },
            isDisabled(){
                return this.disabled||this.field.disabled||this.field.readOnly;
            },
        },
        watch: {
            list:{
                handler(list) {
                    this.$emit('update:value', JSON.stringify(list));
                },
                deep: true,
                immediate: true,
            },
            isInit:{
                handler(val) {
                    if(val){
                        this.$nextTick(()=>{
                            this.$refs['tablefieldeditcurdtable'].getActionWidthByProps()
                        })
                    }
                },
                immediate: true,
            }
        },
        methods:{
            refreshTable(){

            },
            pageChange(page){
                // this.pagination.current = page;
                // this.myFilters.page = page;
                // this.fetch();
            },
            pageSizeChange(pageSize){
                // this.pagination.pageSize = pageSize;
                // this.myFilters.pageSize = pageSize;
                // this.pageChange(1);
            },
            sorterChange(dataIndex,direction){
                // this.myFilters.sortField=dataIndex;
                // this.myFilters.sortOrder=direction;
                // this.pageChange(1);
            },
            openShow(row){
                this.showInfo=row;
                this.showLook=true
            },
            deleteRow(row){
                let idStr=row.id.toString();
                for(let i in this.list){
                    if(this.list[i].id.toString()===idStr){
                        this.list.splice(i,1);
                    }
                }
            },
            openEdit(row){
                this.setEditForm(row);
                this.editShow=true
            },
            clickEditSub(done){
                this.editSub(null,done);
            },
            editSub(option,done){
                option=option||{};
                this.$refs.tableEditPubForm.validate(async (errors)=>{
                    if(errors&&Object.keys(errors).length>0){
                        this.$message.warning(errors[Object.keys(errors)[0]].message);
                        if(done){
                            done(false);
                        }
                        return;
                    }
                    for(let i in this.editData.groupFields){
                        if(this.$refs['tableEditFieldGroup'+i]){
                            let form=this.$refs['tableEditFieldGroup'+i];
                            if(typeof form.validateListForm==='undefined'&&form[0]&&typeof form[0].validateListForm==='function'){
                                form=form[0];
                            }
                            if(await form.validateListForm()===false){
                                return;
                            }
                        }
                    }
                    //因为数据有其他处理，所以这里返回过来的值就不再赋值到form中了
                    if(this.editForm.id){
                        for(let i in  this.list){
                            if(this.list[i].id.toString()===this.editForm.id.toString()){
                                this.list[i]=JSON.parse(JSON.stringify(this.editForm));
                                break;
                            }
                        }
                    }else{
                        this.addMaxId++;
                        const listData=JSON.parse(JSON.stringify(this.editForm));
                        listData.id=-this.addMaxId;
                        this.list.unshift(listData);
                    }

                    if(option.success){
                        option.success(res)
                    }
                    this.$refs['tablefieldeditcurdtable'].getActionWidthByProps()
                    if(done){
                        done(true);
                    }else{
                        this.editShow=false;
                    }
                });
            },
            showAdd(){
                this.setEditForm({});
                this.editShow=true
            },
            setEditForm(row){
                this.editInfo=row;
                if(row&&row.id&&row.id<0){
                    this.editForm=row;
                }else{
                    const form={};
                    this.field.fields.forEach(function(field){
                        form[field.name]=row&&typeof row[field.name]!=='undefined'?row[field.name]:'';
                    })

                    if(row&&row.id){
                        form.id=row.id;
                    }
                    this.editForm=form;
                }
            },
            checkShowGroup(groupFieldItems){
                for(let i in groupFieldItems){
                    if(groupFieldItems[i].editShow&&!this.editData.fieldHideList[groupFieldItems[i].name]){
                        return true;
                    }
                }
                return false;
            },
        },
        template:`<div>
                    <div class="table-field-box" v-if="isInit">
                        <div class="table-field-box-add" v-if="!isDisabled"><a-button shape="circle" size="small" @click="showAdd"><icon-plus></icon-plus></a-button></div>
                        <div class="table-field-box-table" v-show="list.length>0">
                            <curd-table
                                :data="list"
                                :pagination="{
                                    pageSize: field.pageSize,
                                    total:0,
                                    current:1,
                                    showPageSize:false,
                                }"
                                :loading="tableLoading"
                                :list-columns="listColumns"
                                :can-edit="canEdit&&!isDisabled"
                                :can-del="canDel&&!isDisabled"
                                :action-def-width="actionDefWidth"
                                :show-create-time="false"
                                :show-action="field.showAction"
                                @refresh-table="refreshTable"
                                @page-change="pageChange"
                                @page-size-change="pageSizeChange"
                                @sorter-change="sorterChange"
                                @open-show="openShow"
                                @on-delete="deleteRow"
                                @open-edit="openEdit"
                                ref="tablefieldeditcurdtable">
                            </curd-table>
                        </div>
                            <a-modal modal-class="table-field-show-modal" width="95%" v-model:visible="editShow" unmount-on-close :title="editInfo&&editInfo.id?'修改'+field.title:'添加'+field.title" :on-before-ok="clickEditSub">
                                <div class="vuecurd-def-box">
                                        <a-form :model="editForm" :label-col-props="editLabelCol" :wrapper-col-props="editWrapperCol" ref="tableEditPubForm">
                                            <template v-for="(groupFieldItems,groupTitle) in editData.groupFields">
                                                <template v-if="editShowGroup">
                                                    <fieldset class="field-group-fieldset show-group" v-show="checkShowGroup(groupFieldItems)">
                                                        <div class="legend-box">
                                                            <legend>{{groupTitle}}</legend>
                                                        </div>
                                                        <field-group-item
                                                            :list-field-label-col="editLabelCol"
                                                            :list-field-wrapper-col="editWrapperCol"
                                                            :group-field-items="groupFieldItems"
                                                            :info="editInfo"
                                                            v-model:field-hide-list="editData.fieldHideList"
                                                            v-model:form="editForm"
                                                            @submit="editSub($event)"
                                                            :ref="'tableEditFieldGroup'+groupTitle"></field-group-item>
                                                    </fieldset>
                                                </template>
                                                <template v-else>
                                                    <field-group-item
                                                        :list-field-label-col="editLabelCol"
                                                        :list-field-wrapper-col="editWrapperCol"
                                                        :group-field-items="groupFieldItems"
                                                        :info="editInfo"
                                                        v-model:field-hide-list="editData.fieldHideList"
                                                        v-model:form="editForm"
                                                        @submit="editSub($event)"
                                                        :ref="'tableEditFieldGroup'+groupTitle"></field-group-item>
                                                </template>
                                            </template>
                                        </a-form>
                                </div>
                            </a-modal>
                            <a-modal modal-class="table-field-show-modal" width="95%" v-model:visible="showLook" unmount-on-close :title="'查看'+field.title" :footer="false">
                                <div class="vuecurd-def-box vuecurd-show-def-box">
                                    <template v-for="(groupFieldItems,groupTitle) in showData.groupFields">
                                        <fieldset class="field-group-fieldset" :class="{'show-group':showData.haveGroup}">
                                            <div class="legend-box">
                                                <legend>{{groupTitle}}</legend>
                                            </div>
                                            <div class="show-group-field-rows">
                                                <template v-for="showField in groupFieldItems">
                                                    <a-row class="row" v-if="!showField.showUseComponent">
                                                        <a-col class="l" span="4">{{showField.title}}：</a-col>
                                                        <a-col class="r" span="20">
                                                            <curd-show-field :field="showField" :info="showInfo"></curd-show-field>
                                                        </a-col>
                                                    </a-row>
                                                    <component
                                                        v-else-if="fieldComponents['VueCurdShow'+showField.type]"
                                                        :is="'VueCurdShow'+showField.type"
                                                        :field="showField"
                                                        :info="showInfo"
                                                    ></component>
                                                </template>
                                            </div>
                                        </fieldset>
                                    </template>
                                </div>
                            </a-modal>
                    </div>
                </div>`,
    };
});