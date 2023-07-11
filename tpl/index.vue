{block name="extend"}{/block}
<style>
body{
    background-color: #f0f2f5;
    padding: 24px;
}
.box {
    background-color: #f0f2f5;
}
body.arco-modal-iframe-body{
    background-color: #fff;
    padding: 0;
}
body.arco-modal-iframe-body .box{
    background-color: #fff;
}
/**body.arco-modal-iframe-body .box>.body>.curd-table-box{
    padding: 0 1px 24px 1px;
}
body.arco-modal-iframe-body .box>.head:not(.have-filter-items)+.body .arco-table-list-toolbar{
    padding-right: 18px;
}
body.arco-modal-iframe-body .box>.head.head:not(.have-filter-items) .filter-sub-btn-box{
    right: 26px;
}
body.arco-modal-iframe-body .box>.body>.arco-table-list-toolbar>.arco-table-list-toolbar-container{
    padding: 0 16px;
}**/
.head {
    padding-top: 16px;
    padding-bottom: 16px;
    background-color: #fff;
    margin-bottom: 16px;
    border: 1px solid #91d5ff;
    border-radius: 4px;
}
.head:not(.have-filter-items){
    border: 0;
    padding: 0;
    margin: 0;
    height: 0;
    background-color: transparent;
    position: relative;
}
.head:not(.have-filter-items) .filter-sub-btn-box{
    position: absolute;
    right: 36px;
    top: 27px;
    animation: bounce-in .8s;
}
.head:not(.have-filter-items) .filter-sub-btn-box .arco-dropdown-link{
    font-size: 22px!important;
}
.head:not(.have-filter-items) .filter-sub-btn-box .arco-divider-text{
    background-color: transparent;
}

.head:not(.have-filter-items)+.body .arco-table-list-toolbar{
    padding-right: 28px;
}


.body {
    background-color: #fff;
    border-radius: 6px;
}

.foot {
    min-height: 24px; /**给底部空间**/
}
.arco-table-list-toolbar-title {
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
body{
    background: #fff;
}
.box {
    background-color: #fff;
    display: flex;
    flex-direction: column;
}
.box .head{
    margin: 0 12px 12px 12px;
    border-radius: 6px;
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
    box-sizing: border-box;
    padding: 0 0 6px 0;
}
.childTitle{
    color: rgba(0, 0, 0, .85);
    font-size: 16px;
}
</style>
{/if}
{block name="style"}{/block}
{if isset($leftCate)&&!empty($leftCate['show'])}
<div class="box-base" style="display: flex;width: 100%">
{/if}
<div v-if="leftCate.show" class="left-cate-div-parent">
    <div class="left-cate-div" :style="{width:leftCate.width}">
        <div class="arco-pro-table-list-toolbar-title">
            <div class="arco-pro-table-list-toolbar-title-text">{{leftCate.title}}</div>
            <template v-if="leftCate.addBtn">
                <a-tooltip v-if="leftCate.addBtn.btnTitle" position="right" :content="leftCate.addBtn.btnTitle">
                    <a-button @click="leftCateOpenAdd" type="dashed"><template #icon><icon-plus></icon-plus></template></a-button>
                </a-tooltip>
                <a-button v-else @click="leftCateOpenAdd" type="dashed"><template #icon><icon-plus></icon-plus></template></a-button>
            </template>
        </div>
        <div class="left-cate-tool">
            <div>
                <a-tooltip>
                    <template #content>刷新</template>
                    <a-button shape="circle" type="dashed" @click="leftCateRefresh" :disabled="leftCateObj.loading">
                        <template #icon>
                            <icon-refresh></icon-refresh>
                        </template>
                    </a-button>
                </a-tooltip>
            </div>
            <div style="flex: 1"><a-input-search v-model:model-value="leftCateObj.searchValue" placeholder="输入关键字" allow-clear :loading="leftCateObj.loading"></a-input-search></div>
            <div>
                <a-tooltip>
                    <template #content>展开全部</template>
                    <a-button shape="circle" type="dashed" @click="leftCateExpand" :disabled="leftCateObj.loading">
                        <template #icon>
                            <icon-expand></icon-expand>
                        </template>
                    </a-button>
                </a-tooltip>
            </div>

            <div>
                <a-tooltip>
                    <template #content>收起全部</template>
                    <a-button shape="circle" type="dashed" @click="leftCateShrink" :disabled="leftCateObj.loading">
                        <template #icon>
                            <icon-shrink></icon-shrink>
                        </template>
                    </a-button>
                </a-tooltip>
            </div>

        </div>
        <div class="left-cate-list">
            <a-spin :loading="leftCateObj.loading">
                <a-tree :data="leftCate.list" :field-names="{key:'value'}" v-model:expanded-keys="leftCateObj.expandedKeys" v-model:checked-keys="leftCateObj.selectedKeys" @select="leftCateSelect">
                    <template #title="item">
                        <template v-if="leftCate.editBtn||leftCate.rmUrl">
                            <a-popover position="bl" trigger="contextMenu" v-model:popup-visible="leftCateObj.showTools[item.value]">
                                <template #content>
                                    <div v-if="leftCate.editBtn"><a @click="leftCateOpenEdit(item)"><icon-edit></icon-edit>&nbsp;{{leftCate.editBtn.btnTitle||'修改'}}</a></div>
                                    <a-divider v-if="leftCate.editBtn&&leftCate.rmUrl" style="margin: 6px 0"></a-divider>
                                    <div v-if="leftCate.rmUrl"><a class="red" @click="leftCateDeleteRow(item)"><icon-delete></icon-delete>&nbsp;删除</a></div>
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

<div class="box" :style="leftCate.show?'flex:1;width:0':''">
    <div class="head" :class="{'have-filter-items':haveFielterShow}" v-if="showFilter">
        <curd-filter ref="filter"
                     @search="doFilter"
                     :filter-config="filterBase.filterConfig"
                     :name="filterBase.name" :class="filterBase.class"
                     :title="filterBase.title"
                     :childs="childs"
                     :filter-values="filterBase.filterValues"
                     :loading="loading"
                     @have-fielter-show-change="haveFielterShow=$event"
        ></curd-filter>
    </div>
    <div class="body">
        <div class="arco-table-list-toolbar" v-if="showTableTool">
            <div class="arco-table-list-toolbar-container">
                <div class="arco-table-list-toolbar-left">
                    {block name="toolTitleLeft"}{/block}

                    <a-space size="mini">
                        <template #split>
                            <a-divider direction="vertical" style="margin: 0 6px"></a-divider>
                        </template>

                        <a-button v-for="(btn,index) in toolTitleLeftBtns" :key="keyValueStr(btn)" :type="btn.btnType==='a'?'text':btn.btnType" :status="btn.btnStatus" @click="openOtherBtn(btn)" class="my-other-btn">
                            <template #icon v-if="btn.btnSvg"><span v-html="btn.btnSvg" role="img" aria-label class="anticon"></span></template>
                            <span> {{btn.btnTitle}}</span>
                        </a-button>


                        {block name="toolTitleMid"}
                        {if $childTpl}
                        <div class="childTitle">{{titleByLeftCateSelect('详细列表')}}</div>
                        {else/}
                        <div class="arco-table-list-toolbar-title" :style="{width: leftCate.show?'calc('+titleByLeftCateSelect('{$title}').length+'em * 1.08 + 2px)':'auto'}">
                            <div style="overflow: hidden;white-space: nowrap;text-overflow: ellipsis;max-width: 100%;">{{titleByLeftCateSelect('{$title}')}}</div>
                        </div>
                        {/if}
                        {/block}


                        <template v-if="canDel&&delSelectedIds.length>0">
                            <div class="arco-space-item">
                                <a-divider type="vertical"></a-divider>
                            </div>
                            <a-popconfirm
                                position="left"
                                :content="'您确定要删除勾选的这'+delSelectedIds.length+'条数据吗？'"
                                @ok="delSelectedRows"
                            >
                                <div class="arco-space-item">
                                    <a-button type="primary" status="danger">
                                        <template #icon><icon-delete></icon-delete></template>
                                        <span> 批量删除 ({{delSelectedIds.length}}条数据)</span>
                                    </a-button>
                                </div>
                            </a-popconfirm>
                        </template>



                        <a-button v-for="(btn,index) in toolTitleRightBtns" :key="keyValueStr(btn)" :type="btn.btnType==='a'?'text':btn.btnType" :status="btn.btnStatus" @click="openOtherBtn(btn)" class="my-other-btn">
                            <template #icon v-if="btn.btnSvg"><span v-html="btn.btnSvg" role="img" aria-label class="anticon"></span></template>
                            <span> {{btn.btnTitle}}</span>
                        </a-button>

                        {block name="toolTitleRight"}{/block}
                    </a-space>
                </div>
                <div class="arco-table-list-toolbar-right">
                    <a-space size="mini">
                        <template #split>
                            <a-divider direction="vertical" style="margin: 0 6px"></a-divider>
                        </template>
                        {block name="toolBtnLeft"}{/block}
                        <a-button v-for="(btn,index) in toolBtnLeftBtns" :key="keyValueStr(btn)" :type="btn.btnType==='a'?'text':btn.btnType" :status="btn.btnStatus" @click="openOtherBtn(btn)" class="my-other-btn">
                            <template #icon v-if="btn.btnSvg"><span v-html="btn.btnSvg" role="img" aria-label class="anticon"></span></template>
                            <span> {{btn.btnTitle}}</span>
                        </a-button>

                        <template v-if="canAdd">
                            {block name="toolAddBtn"}
                            <a-button type="{$addBtn['btnType']==='a'?'text':$addBtn['btnType']}" status="{$addBtn['btnStatus']}" @click="openAdd">
                                <template #icon><icon-plus></icon-plus></template>
                                <span> {$addBtn.btnTitle}</span>
                            </a-button>
                            {/block}
                        </template>

                        <template v-if="auth.importExcelTpl&&auth.downExcelTpl">
                            <a-dropdown-button type="primary" status="success" @click="importExcelTpl">
                                <svg width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2292"><path d="M601.152 708.288 400 568 344 568l0 112-224 0 0 112 224 0 0 112L400 904l201.152-140.288C624 749.504 624 722.496 601.152 708.288L601.152 708.288zM891.264 331.2 638.656 76.864C630.528 68.608 619.456 64 607.936 64L232 64C196.032 64 176 83.712 176 120L176 512 288 512 288 176l280 0 0 168c0 24.192 32 56 56 56l168 0 0.768 448L624 848 624 960l224 0c35.968 0 56-19.712 56-56L904 362.176C904 350.528 899.392 339.392 891.264 331.2L891.264 331.2z" p-id="2293"></path></svg>&nbsp;Excel导入数据
                                <template #content>
                                    <a-doption @click="downExcelTpl"><template #icon><icon-download></icon-download></template>Excel模板下载</a-doption>
                                </template>
                            </a-dropdown-button>
                        </template>
                        <template v-else>
                            <a-button v-if="auth.importExcelTpl" type="primary" status="warning"  @click="importExcelTpl">
                                <template #icon><icon-import></icon-import></template>
                                <span> Excel导入数据</span>
                            </a-button>
                            <a-button v-if="auth.downExcelTpl" type="primary" status="success"  @click="downExcelTpl">
                                <template #icon><icon-download></icon-download></template>
                                <span> Excel模板下载</span>
                            </a-button>
                        </template>

                        <a-button v-if="auth.export" @click="exportData" :disabled="!pagination.total">
                            <template #icon><icon-export></icon-export></template>
                            <span> 导出当前数据</span>
                        </a-button>

                        <a-button v-for="(btn,index) in toolBtnRightBtns" :key="keyValueStr(btn)" :type="btn.btnType==='a'?'text':btn.btnType" :status="btn.btnStatus" @click="openOtherBtn(btn)" class="my-other-btn">
                            <template #icon v-if="btn.btnSvg"><span v-html="btn.btnSvg" role="img" aria-label class="anticon"></span></template>
                            <span> {{btn.btnTitle}}</span>
                        </a-button>

                        {block name="toolBtnRight"}{/block}

                        <a-tooltip>
                            <template #content>刷新</template>
                            <icon-refresh @click="refreshTable" style="cursor: pointer;font-size: 18px"></icon-refresh>
                        </a-tooltip>

                        <span class="filter-btn-placeholder" v-if="!haveFielterShow"></span>
                    </a-space>

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
                        v-model:selected-keys="selectedRowKeys"
                        :field-step-config="fieldStepConfig"
                        :action-def-width="actionDefWidth"
                        :show-create-time="showCreateTime"
                        :children-column-name="childrenColumnName"
                        :indent-size="indentSize"
                        :expand-all-rows="expandAllRows"
                        :is-tree-index="isTreeIndex"
                        @refresh-table="refreshTable"
                        @page-change="pageChange"
                        @page-size-change="pageSizeChange"
                        @sorter-change="sorterChange"
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