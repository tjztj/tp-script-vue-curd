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
    overflow: hidden;
    width: 100%;
    flex: 1;
}
</style>
<div class="box">
<div class="week-box">
    <div class="week-tool-box">
        <div class="week-title">详细列表</div>
        <div>
            <template v-if="auth.edit">
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
        </div>
    </div>
    <div class="week-table-box">
        <curd-table
            ref="CurdTable"
            :data="data"
            :pagination="false"
            :loading="tableLoading"
            :list-columns="listColumns"
            :can-edit="canEdit"
            :can-del="canDel"
            @open-show="openShow"
            @on-delete="deleteRow"
            @open-edit="openEdit">

        </curd-table>
    </div>
    <div class="week-foot-box"></div>
</div>
</div>