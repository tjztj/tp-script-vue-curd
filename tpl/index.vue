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

.foot {
    min-height: 24px; /**给底部空间**/
}
.ant-pro-table-list-toolbar-title {
    font-weight: bold;
    color: rgba(0, 0, 0, .95);
    letter-spacing: 0.08em;
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
              {block name="toolTitleLeft"}{/block}
                <div class="ant-pro-table-list-toolbar-title">{$title}</div>
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
                {block name="toolTitleRight"}{/block}
            </div>
            <div class="ant-pro-table-list-toolbar-right">
                <div class="ant-space ant-space-horizontal ant-space-align-center">
                    {block name="toolBtnLeft"}{/block}

                    <template v-if="auth.add&&auth.stepAdd&&auth.rowAuthAdd">
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
                :action-def-width="actionDefWidth"
                @refresh-table="refreshTable"
                @change="handleTableChange"
                @on-delete="deleteRow"
                @open-edit="openEdit"
                @open-next="openNext"
                @open-show="openShow"
                @open-child-list="openChildList"
                ref="indexcurdtable">
        <!--    配合actionWidth使用-->
        {block name="tableSlot"}{/block}

    </curd-table>

</div>
<!--<div class="foot">-->

<!--</div>-->
</div>

{block name="script"}{/block}