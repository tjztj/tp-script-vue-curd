{block name="extend"}{/block}
<style>
.week-tool-box {
    padding: 24px;
    display: flex;
    align-items: center;
}

#app {
    width: 100%;
}

.box {
    width: 100%;
}

.week-box {
    margin-left: -48px;
    margin-right: -48px;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 25px);
    width: calc(100% + 96px);
}

.week-tool-box .week-title {
    color: rgba(0, 0, 0, .85);
    font-size: 16px;
    padding-right: 24px;
    flex: 1;
}

.week-table-box {
    overflow:auto;
    width: 100%;
    flex: 1;
}
.title-right{
    display: flex;
}
</style>
{block name="style"}{/block}
<div class="box">
<div class="week-box">
    <div class="week-tool-box">
        <div class="week-title">
            详细列表
            <template v-if="canDel&&rowSelection.selectedRowKeys.length>0">
                <a-divider type="vertical"></a-divider>
                <a-popconfirm
                    placement="left"
                    :title="'您确定要删除勾选的这'+rowSelection.selectedRowKeys.length+'条数据吗？'"
                    @confirm="delSelectedRows"
                >
                    <a-button type="danger">
                        <del-outlined></del-outlined>
                        <span> 批量删除 ({{rowSelection.selectedRowKeys.length}}条数据)</span>
                    </a-button>
                </a-popconfirm>
            </template>
            {block name="toolTitleRight"}{/block}
        </div>
        <div class="title-right">
            {block name="toolBtnLeft"}{/block}
            <template v-if="auth.add&&auth.stepAdd">
                <a-button type="primary" @click="openAdd">
                    <plus-outlined></plus-outlined>
                    <span> 添加</span>
                </a-button>
                <a-divider type="vertical"></a-divider>
            </template>

            <template v-if="auth.importExcelTpl">
                <a-button type="warning" @click="importExcelTpl">
                    <file-excel-outlined></file-excel-outlined>
                    <span> Excel导入数据</span>
                </a-button>

                <a-divider type="vertical"></a-divider>
            </template>
            <template v-if="auth.downExcelTpl">
                <a-button type="info" @click="downExcelTpl">
                    <download-outlined></download-outlined>
                    <span> Excel模板下载</span>
                </a-button>
            </template>
            {block name="toolBtnRight"}{/block}
            <div class="ant-pro-table-list-toolbar-divider">
                <a-divider type="vertical"></a-divider>
            </div>
            <div class="ant-pro-table-list-toolbar-setting-item">
                <a-tooltip>
                    <template #title>刷新</template>
                    <reload-outlined @click="refreshTable"></reload-outlined>
                </a-tooltip>
            </div>
        </div>
    </div>
    <div class="week-table-box">
        <curd-table
            ref="CurdTable"
            :data="data"
            :pagination="pagination"
            :loading="tableLoading"
            :list-columns="listColumns"
            :can-edit="canEdit"
            :can-del="canDel"
            :row-selection="canDel?rowSelection:null"
            :field-step-config="fieldStepConfig"
            :action-def-width="actionDefWidth"
            @refresh-table="refreshTable"
            @change="handleTableChange"
            @open-show="openShow"
            @on-delete="deleteRow"
            @open-edit="openEdit"
            @open-next="openNext">
            {block name="tableSlot"}{/block}
        </curd-table>
    </div>
    <div class="week-foot-box"></div>
</div>
</div>

{block name="script"}{/block}