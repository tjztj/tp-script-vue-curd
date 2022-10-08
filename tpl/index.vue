{block name="extend"}{/block}
<style>
.box {
    background-color: #f0f2f5;
}

.head {
    padding-top: 18px;
    padding-bottom: 18px;
    background-color: #fff;
    margin-bottom: 18px;
    border: 1px solid #91d5ff;
    border-radius: 2px;
}

.body {
    background-color: #fff;
    border-radius: 2px;
}

.foot {
    min-height: 24px; /**给底部空间**/
}
.ant-pro-table-list-toolbar-title {
    font-weight: bold;
    color: rgba(0, 0, 0, .95);
    letter-spacing: 0.08em;
}

@media screen and (max-width: 820px) {
    .curd-filter-box .filter-box-div{
        grid-template-columns: auto;
    }
    .curd-filter-box .filter-item-box {
        padding-left: 24px !important;
    }
}
</style>
{if $childTpl}
<style>
.box {
    background-color: #fff;
    margin-left: -48px;
    margin-right: -48px;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 25px);
    width: calc(100% + 96px);
}
.box .head{
    margin: 0 12px 12px 12px;
    border-radius: 4px;
}
.box .body{
    width: 100%;
    flex: 1;
    display: flex;
    flex-direction: column;
    height: 0;
}
.box .body .curd-table-box{
    overflow: auto;
    width: 100%;
    flex: 1;
    height: 0;
}
.childTitle{
    color: rgba(0, 0, 0, .85);
    font-size: 16px;
}
</style>
{/if}
{block name="style"}{/block}
{if isset($leftCate)&&!empty($leftCate['show'])}
<div class="box-base" style="display: flex">
{/if}
<div v-if="leftCate.show" class="left-cate-div-parent">
    <div class="left-cate-div" :style="{width:leftCate.width}">
        <div class="ant-pro-table-list-toolbar-title">
            <div class="ant-pro-table-list-toolbar-title-text">{{leftCate.title}}</div>
            <a-tooltip v-if="leftCate.addBtn" placement="right" :title="leftCate.addBtn.btnTitle">
                <a-button @click="leftCateOpenAdd" type="dashed">
                    <template #icon>
                        <plus-outlined></plus-outlined>
                    </template>
                </a-button>
            </a-tooltip>
        </div>
        <div class="left-cate-tool">
            <div>
                <a-tooltip>
                    <template #title>刷新</template>
                    <a-button shape="circle" type="dashed" @click="leftCateRefresh" :disabled="leftCateObj.loading">
                        <template #icon>
                            <reload-outlined></reload-outlined>
                        </template>
                    </a-button>
                </a-tooltip>
            </div>
            <div style="flex: 1"><a-input-search v-model:value="leftCateObj.searchValue" placeholder="输入关键字" allow-clear :disabled="leftCateObj.loading"></a-input-search></div>
            <div>
                <a-tooltip>
                    <template #title>展开全部</template>
                    <a-button shape="circle" type="dashed" @click="leftCateExpand" :disabled="leftCateObj.loading">
                        <template #icon>
                            <arrows-alt-outlined></arrows-alt-outlined>
                        </template>
                    </a-button>
                </a-tooltip>
            </div>

            <div>
                <a-tooltip>
                    <template #title>收起全部</template>
                    <a-button shape="circle" type="dashed" @click="leftCateShrink" :disabled="leftCateObj.loading">
                        <template #icon>
                            <shrink-outlined></shrink-outlined>
                        </template>
                    </a-button>
                </a-tooltip>
            </div>

        </div>
        <div class="left-cate-list">
            <a-spin :spinning="leftCateObj.loading">
                <a-tree :tree-data="leftCate.list" :replace-fields="{key:'value'}" v-model:expanded-keys="leftCateObj.expandedKeys" v-model:checked-keys="leftCateObj.selectedKeys" @select="leftCateSelect">
                    <template #title="item">
                        <template v-if="leftCate.editBtn||leftCate.rmUrl">
                            <a-popover placement="bottomLeft" trigger="contextmenu" v-model:visible="leftCateObj.showTools[item.value]">
                                <template #content>
                                    <div v-if="leftCate.editBtn"><a @click="leftCateOpenEdit(item)"><edit-outlined></edit-outlined>&nbsp;{{leftCate.editBtn.btnTitle||'修改'}}</a></div>
                                    <a-divider v-if="leftCate.editBtn&&leftCate.rmUrl" style="margin: 6px 0"></a-divider>
                                    <div v-if="leftCate.rmUrl"><a class="red" @click="leftCateDeleteRow(item)"><del-outlined></del-outlined>&nbsp;删除</a></div>
                                </template>
                                <span v-if="leftCateObj.searchValue.trim()!==''&&item.title.indexOf(leftCateObj.searchValue.trim()) > -1">
                                    {{ item.title.substr(0, item.title.indexOf(leftCateObj.searchValue.trim())) }}
                                    <span style="color: #f50">{{ leftCateObj.searchValue.trim() }}</span>
                                    {{ item.title.substr(item.title.indexOf(leftCateObj.searchValue.trim()) + leftCateObj.searchValue.trim().length) }}
                                </span>
                                <span v-else>{{ item.title }}</span>
                            </a-popover>
                        </template>
                        <template v-else>
                            <span v-if="item.title.indexOf(leftCateObj.searchValue.trim()) > -1">
                                {{ item.title.substr(0, item.title.indexOf(leftCateObj.searchValue.trim())) }}
                                <span style="color: #f50">{{ leftCateObj.searchValue.trim() }}</span>
                                {{ item.title.substr(item.title.indexOf(leftCateObj.searchValue.trim()) + leftCateObj.searchValue.trim().length) }}
                            </span>
                            <span v-else>{{ item.title }}</span>
                        </template>

                    </template>
                </a-tree>
            </a-spin>
        </div>
    </div>
</div>

<div class="box" :style="leftCate.show?'flex:1':''">
<div class="head" v-if="showFilter">
    <curd-filter ref="filter"
                 @search="doFilter"
                 :filter-config="filterBase.filterConfig"
                 :name="filterBase.name" :class="filterBase.class"
                 :title="filterBase.title"
                 :childs="childs"
                 :filter-values="filterBase.filterValues"
                 :loading="loading"></curd-filter>
</div>
<div class="body">
    <div class="ant-pro-table-list-toolbar" v-if="showTableTool">
        <div class="ant-pro-table-list-toolbar-container">
            <div class="ant-pro-table-list-toolbar-left">
                {block name="toolTitleLeft"}{/block}
                <div style="line-height: 64px;">
                    <template v-for="(btn,index) in toolTitleLeftBtns">
                        <a-button :type="btn.btnType==='a'?'link':btn.btnType" @click="openOtherBtn(btn)" class="my-other-btn">
                            <span v-html="btn.btnSvg" role="img" aria-label class="anticon"></span>
                            <span> {{btn.btnTitle}}</span>
                        </a-button>
                        <div class="ant-space-item" style="display: inline-block">
                            <a-divider type="vertical"></a-divider>
                        </div>
                    </template>
                </div>


                {block name="toolTitleMid"}
                {if $childTpl}
                <div class="childTitle">{{titleByLeftCateSelect('详细列表')}}</div>
                {else/}
                <div class="ant-pro-table-list-toolbar-title" :style="{width: leftCate.show?'calc('+titleByLeftCateSelect('{$title}').length+'em * 1.08 + 2px)':'auto'}">
                    <div style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;max-width: 100%;">{{titleByLeftCateSelect('{$title}')}}</div>
                </div>
                {/if}
                {/block}

                <template v-if="canDel&&delSelectedIds.length>0">
                    <div class="ant-space-item">
                        <a-divider type="vertical"></a-divider>
                    </div>
                    <a-popconfirm
                        placement="left"
                        :title="'您确定要删除勾选的这'+delSelectedIds.length+'条数据吗？'"
                        @confirm="delSelectedRows"
                    >
                        <div class="ant-space-item">
                            <a-button type="danger">
                                <del-outlined></del-outlined>
                                <span> 批量删除 ({{delSelectedIds.length}}条数据)</span>
                            </a-button>
                        </div>
                    </a-popconfirm>
                </template>

                <div style="line-height: 64px;">
                    <template v-for="(btn,index) in toolTitleRightBtns">
                        <div class="ant-space-item" style="display: inline-block">
                            <a-divider type="vertical"></a-divider>
                        </div>
                        <a-button :type="btn.btnType==='a'?'link':btn.btnType" @click="openOtherBtn(btn)" class="my-other-btn">
                            <span v-html="btn.btnSvg" role="img" aria-label class="anticon"></span>
                            <span> {{btn.btnTitle}}</span>
                        </a-button>
                    </template>
                </div>

                {block name="toolTitleRight"}{/block}


            </div>
            <div class="ant-pro-table-list-toolbar-right">
                <div class="ant-space ant-space-horizontal ant-space-align-center">
                    {block name="toolBtnLeft"}{/block}

                    <template v-for="(btn,index) in toolBtnLeftBtns">
                        <div class="ant-space-item">
                            <a-button :type="btn.btnType==='a'?'link':btn.btnType" @click="openOtherBtn(btn)" class="my-other-btn">
                                <span v-html="btn.btnSvg" role="img" aria-label class="anticon"></span>
                                <span> {{btn.btnTitle}}</span>
                            </a-button>
                        </div>
                        <div class="ant-space-item">
                            <a-divider type="vertical"></a-divider>
                        </div>
                    </template>


                    <template v-if="canAdd">
                        {block name="toolAddBtn"}
                        <div class="ant-space-item">
                            <a-button type="{$addBtn['btnType']==='a'?'link':$addBtn['btnType']}" @click="openAdd">
                                <plus-outlined></plus-outlined>
                                <span> {$addBtn.btnTitle}</span>
                            </a-button>
                        </div>
                        <div class="ant-space-item">
                            <a-divider type="vertical"></a-divider>
                        </div>
                        {/block}
                    </template>

                    <template v-if="auth.importExcelTpl">
                        <div class="ant-space-item">
                            <a-button type="warning" @click="importExcelTpl">
                                <file-excel-outlined></file-excel-outlined>
                                <span> Excel导入数据</span>
                            </a-button>
                        </div>
                        <div class="ant-space-item">
                            <a-divider type="vertical"></a-divider>
                        </div>
                    </template>

                    <template v-if="auth.downExcelTpl">
                        <div class="ant-space-item">
                            <a-button type="info" @click="downExcelTpl">
                                <download-outlined></download-outlined>
                                <span> Excel模板下载</span>
                            </a-button>
                        </div>
                    </template>

                    <template v-if="auth.export">
                        <div class="ant-space-item" v-if="auth.downExcelTpl">
                            <a-divider type="vertical"></a-divider>
                        </div>
                        <div class="ant-space-item">
                            <a-button class="azure-blue" @click="exportData" :disabled="!pagination.total">
                                <export-outlined></export-outlined><span> 导出当前数据</span>
                            </a-button>
                        </div>
                    </template>


                    <template v-for="(btn,index) in toolBtnRightBtns">
                        <div class="ant-space-item">
                            <a-divider type="vertical"></a-divider>
                        </div>
                        <div class="ant-space-item">
                            <a-button :type="btn.btnType==='a'?'link':btn.btnType" @click="openOtherBtn(btn)" class="my-other-btn">
                                <span v-html="btn.btnSvg" role="img" aria-label class="anticon"></span>
                                <span> {{btn.btnTitle}}</span>
                            </a-button>
                        </div>
                    </template>

                    {block name="toolBtnRight"}{/block}
                </div>
                <div class="ant-pro-table-list-toolbar-divider">
                    <a-divider type="vertical"></a-divider>
                </div>
                <div class="ant-pro-table-list-toolbar-setting-item">
                    <a-tooltip>
                        <template #title>刷新</template>
                        <reload-outlined @click="refreshTable"></reload-outlined>
                    </a-tooltip>
                </div>
                <div class="ant-pro-table-list-toolbar-setting-item">
                </div>
            </div>
        </div>
    </div>
    <div :class="{'curd-table-box':true,'table-color-them':tableThemIsColor}">
        <curd-table :data="data"
                    :pagination="pagination"
                    :loading="loading"
                    :list-columns="listColumns"
                    :childs="childs"
                    :can-add="canAdd"
                    :can-edit="canEdit"
                    :can-del="canDel"
                    :action-width="actionWidth"
                    :row-selection="showMultipleSelection===true||(canDel&&showMultipleSelection===null)?rowSelection:null"
                    :field-step-config="fieldStepConfig"
                    :action-def-width="actionDefWidth"
                    :show-create-time="showCreateTime"
                    :set-scroll-y="!!childTpl"
                    :children-column-name="childrenColumnName"
                    :indent-size="indentSize"
                    :expand-all-rows="expandAllRows"
                    :is-tree-index="isTreeIndex"
                    @refresh-table="refreshTable"
                    @change="handleTableChange"
                    @on-delete="deleteRow"
                    @open-edit="openEdit"
                    @open-add-children="openAddChildren"
                    @open-next="openNext"
                    @open-show="openShow"
                    @open-child-list="openChildList"
                    @refresh-id="refreshId"
                    @refresh-table="refreshTable"
                    ref="indexcurdtable">
            <!--    配合actionWidth使用-->
            {block name="tableSlot"}{/block}

        </curd-table>
    </div>
</div>
<!--<div class="foot">-->

<!--</div>-->
</div>
{if isset($leftCate)&&!empty($leftCate['show'])}
</div>
{/if}
{block name="script"}{/block}