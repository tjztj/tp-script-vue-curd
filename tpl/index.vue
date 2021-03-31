{block name="extend"}{/block}
<style>
.box {
    background-color: #f0f2f5;
}

.head {
    padding-top: 12px;
    padding-bottom: 12px;
    background-color: #fff;
    margin-bottom: 12px;
    border: 1px solid #91d5ff;
    border-radius: 2px;
}

.body {
    background-color: #fff;
    border-radius: 2px;
}

.filter-box {
    display: grid;
    grid-template-columns: 1fr 1fr;
    align-items: stretch;
}

.filter-box + .filter-box, .filter-box + .filter-box-title {
    border-top: 4px #f0f2f5 solid;
    padding-top: 8px;
}

.filter-item-box:nth-last-of-type(2):nth-of-type(odd), .filter-item-box:last-of-type {
    border-bottom: 0;
}

.filter-item-box:nth-of-type(odd) {
    padding-left: 24px;
}

.filter-item-box:nth-of-type(even) {
    padding-left: 8px;
}

.filter-item-box {
    padding-bottom: 6px;
    padding-top: 6px;
    border-bottom: 1px solid #e6f7ff;
    transition: all .3s ease;
}

.filter-item {
    display: flex;
    align-items: center;
    height: 100%;
}

.filter-item-l {
    padding: .2em .5em .2em 0;
    color: #000;
    width: 7em;
    text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.05);
}

.filter-item-r {
    flex: 1;
}

.filter-item-check-item {
    display: inline-block;
    color: rgba(0, 0, 0, .5);
    margin: 0 .1em;
    cursor: pointer;
    border: 1px solid transparent;
    padding: 0 .5em;
    border-radius: 2px;
    transition: all .3s;
}

.input-value-div, .region-value-div {
    margin: 0 .1em;
    padding: .05em .5em;
}

.filter-item-check-item:hover {
    border-color: #bae7ff;
    background-color: #e6f7ff;
}

.filter-item-check-item.active {
    color: #40a9ff;
}

.filter-item-check-item.active:hover {
    border-color: transparent;
    background-color: transparent;
    color: #096dd9;
}

.filter-item-check-item.active .ant-calendar-range-picker-input, .filter-item-check-item.active .ant-input, .filter-item-check-item.active .ant-input-sm {
    color: #096dd9;
}

.filter-item-check-item .ant-input-group .ant-btn-sm, .input-value-div .ant-input-group .ant-btn-sm {
    padding-top: 1px;
    padding-bottom: 1px;
    height: auto;
}

.filter-item-check-item-value {
    padding: 2px 0;
}

.ant-pro-table-list-toolbar-title {
    font-weight: bold;
    color: rgba(0, 0, 0, .95);
    letter-spacing: 0.08em;
}

.foot {
    min-height: 24px; /**给底部空间**/
}

.ant-dropdown-menu-item > a.filter-select-show-item {
    display: flex;
}

.filter-select-show-title {
    padding-right: 4px;
    flex: 1;
}

.filter-select-show-item .anticon {
    display: none;
    color: #003a8c;
    line-height: inherit;
}

.filter-select-show-item.checked {
    color: #1890ff;
}

.filter-select-show-item.checked .anticon {
    display: inline-block;
}

.filter-box-title {
    color: #bfbfbf;
    line-height: 1em;
    padding-left: 6px;
    font-weight: bold;
}
.filter-select-show-item-title{
    margin-top: 6px;
    color:#bfbfbf;
    border-left: 2px solid #2f54eb;
    padding-left: 4px;
}
</style>
{block name="style"}{/block}
<div class="box">
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
                <div class="ant-pro-table-list-toolbar-title">{$title}</div>
                <template v-if="canDel&&rowSelection.selectedRowKeys.length>0">
                    <div class="ant-space-item">
                        <a-divider type="vertical"></a-divider>
                    </div>
                    <a-popconfirm
                        placement="left"
                        :title="'您确定要删除勾选的这'+rowSelection.selectedRowKeys.length+'条数据吗？'"
                        @confirm="delSelectedRows"
                    >
                        <div class="ant-space-item">
                            <a-button type="danger">
                                <del-outlined></del-outlined>
                                <span> 批量删除 ({{rowSelection.selectedRowKeys.length}}条数据)</span>
                            </a-button>
                        </div>
                    </a-popconfirm>
                </template>
            </div>
            <div class="ant-pro-table-list-toolbar-right">
                <div class="ant-space ant-space-horizontal ant-space-align-center">
                    <template v-if="auth.edit">
                        <div class="ant-space-item">
                            <a-button type="primary" @click="openAdd">
                                <plus-outlined></plus-outlined>
                                <span> 新增</span>
                            </a-button>
                        </div>
                        <div class="ant-space-item">
                            <a-divider type="vertical"></a-divider>
                        </div>
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
    <curd-table :data="data"
                :pagination="pagination"
                :loading="loading"
                :list-columns="listColumns"
                :childs="childs"
                :can-edit="canEdit"
                :can-del="canDel"
                :action-width="actionWidth"
                :row-selection="canDel?rowSelection:null"
                :field-step-config="fieldStepConfig"
                @refresh-table="refreshTable"
                @change="handleTableChange"
                @on-delete="deleteRow"
                @open-edit="openEdit"
                @open-show="openShow"
                @open-child-list="openChildList">
        <!--    配合actionWidth使用-->
        {block name="tableSlot"}{/block}

    </curd-table>

</div>
<!--<div class="foot">-->

<!--</div>-->
</div>

{block name="script"}{/block}