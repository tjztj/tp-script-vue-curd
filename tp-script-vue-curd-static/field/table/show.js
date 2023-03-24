function setup(props, ctx) {
    const guid = window.guid();
    Vue.provide('table-guid', guid);

    const newActionW = Vue.ref(0);
    const columnsVals = Vue.ref([]);
    const isGroup = Vue.ref(false);
    const titleItems = Vue.ref({});
    const listFieldComponents = Vue.ref({});
    const fieldObjs = Vue.ref({});
    const childsObjs = Vue.ref({});

    const scrollX = Vue.ref(undefined);
    const scrollY = Vue.ref(undefined);
    const id = 'pub-default-table-' + guid;
    let onresize;

    const getX = (columnsCount, w) => {
        if (w > 1640) return undefined;
        if (columnsCount <= 3) return w > 370 ? undefined : 420;
        if (columnsCount <= 4) return w > 450 ? undefined : 500;
        if (columnsCount <= 5) return w > 680 ? undefined : 960;
        if (columnsCount < 6) return w > 780 ? undefined : 1080;
        if (columnsCount < 7) return w > 880 ? undefined : 1120;
        if (columnsCount < 8) return w > 960 ? undefined : 1180;
        if (columnsCount < 9) return w > 1020 ? undefined : 1240;
        if (columnsCount < 12) return w > 1460 ? undefined : 1560;
        return 1640;
    };

    const getColumns = (listColumns, fo, lfc, ti, columnsCount) => {
        const groupTitles = Object.keys(listColumns);
        const columns = groupTitles.map((groupTitle) => {
            const children = listColumns[groupTitle].map((item) => {
                fo[item.name] = item;
                const customTitle = 'custom-title-' + item.name;
                ti[customTitle] = item;
                const col = {
                    dataIndex: item.name,
                    name: item.name,
                    titleSlotName: customTitle,
                    fixed: item.listFixed ? item.listFixed : false,
                };
                if (!(item.listEdit && item.listEdit.saveUrl)) {
                    col.ellipsis = true;
                    col.tooltip = true;
                }
                if (fieldComponents['VueCurdIndex' + item.type]) {
                    lfc[item.name] = item;
                    col.slotName = 'field-component-' + item.name;
                } else {
                    col.slotName = 'default-value';
                }
                if (item.listColumnWidth) {
                    col.width = item.listColumnWidth;
                }
                if (item.listSort) {
                    col.sortable = {sortDirections: ['ascend', 'descend'],};
                }
                columnsCount++;
                return col;
            });
            return {title: groupTitle, children};
        });
        isGroup.value = groupTitles.length > 1 || (!listColumns[''] && groupTitles.length > 0);
        if (!isGroup.value && columns[0]) {
            columns = columns[0].children;
        }
        return {columns, columnsCount};
    };

    const createTimeCol = {
        ellipsis: true,
        tooltip: true,
        dataIndex: 'create_time',
        titleSlotName: 'custom-title-create_time',
        slotName: 'create-time',
        width: 152,
        sortable: {sortDirections: ['ascend', 'descend'],},
    };

    const getStepCol = (props) => {
        const stepCol = {
            ellipsis: true,
            dataIndex: 'stepInfo',
            titleSlotName: 'custom-title-step-info',
            slotName: 'step-info',
        };
        if (props.fieldStepConfig.listFixed) {
            stepCol.fixed = props.fieldStepConfig.listFixed;
        }
        if (props.fieldStepConfig.width && props.fieldStepConfig.width > 0) {
            stepCol.width = props.fieldStepConfig.width;
        }
        return stepCol;
    };

    const getActionCol = (props) => {
        const actionW = props.actionDefWidth || (32 + 28);
        const actionCol = {titleSlotName: 'custom-title-action', slotName: 'action', width: actionW, fixed: 'right',};
        return actionCol;
    };

    Vue.watchEffect(() => {
        const listColumns = props.listColumns;
        let columnsCount = 0;
        const fo = {};
        const lfc = {};
        const ti = {};
        const {columns, columnsCount: newColumnsCount} = getColumns(listColumns, fo, lfc, ti, columnsCount);
        columnsCount = newColumnsCount;
        if (props.fieldStepConfig && props.fieldStepConfig.enable && props.fieldStepConfig.listShow === true) {
            const stepCol = getStepCol(props);
            if (props.fieldStepConfig.listFixed) {
                stepCol.width = stepCol.width || 180;
                if (props.showCreateTime === undefined || props.showCreateTime) {
                    columns.push(createTimeCol);
                    columnsCount;
                }
                columns.push(stepCol);
                columnsCount;
            } else {
                columns.push(stepCol);
                columnsCount;
                if (props.showCreateTime === undefined || props.showCreateTime) {
                    columns.push(createTimeCol);
                    columnsCount;
                }
            }
        } else {
            if (props.showCreateTime === undefined || props.showCreateTime) {
                columns.push(createTimeCol);
                columnsCount;
            }
        }
        if (props.showAction !== false) {
            const actionCol = getActionCol(props);
            columns.push(actionCol);
            columnsCount;
        }
        columnsVals.value = columns;
        onresize = () => {
            const w = document.body.querySelector('#' + id).clientWidth;
            scrollX.value = getX(columnsCount, w);
            if (scrollX.value === undefined) {
                const tablePath = '#' + id + '>.curd-table table.arco-table-element';
                if (!document.querySelector('#' + id) || !document.querySelector('#' + id), document.querySelector(tablePath)) {
                    if (!document.querySelector('#' + id + '>.curd-table table') || !document.querySelector('#' + id + '>.curd-table tbody')) {
                        setTimeout(() => {
                            onresize();
                        }, 40);
                    }
                } else {
                    scrollX.value = document.querySelector('#' + id).clientWidth;
                }
            }
            columnsVals.value.forEach((col) => {
                if (typeof col.fixed !== 'undefined') {
                    if (scrollX.value === undefined) {
                        if (typeof col.fixedOld === 'undefined') {
                            col.fixedOld = col.fixed;
                        }
                        col.fixed = false;
                    } else if (typeof col.fixedOld !== 'undefined') {
                        col.fixed = col.fixedOld;
                    }
                }
            });
            if (props.setScrollY) {
                scrollY.value = '100%';
            }
        };
        Vue.nextTick(() => {
            onresize();
            const oldResize = window.onresize || function () {
            };
            window.onresize = (e) => {
                oldResize(e);
                onresize();
            };
        });
        const co = {};
        if (props.childs) {
            props.childs.forEach((v) => {
                co[v.name] = v;
            });
        }
        titleItems.value = ti;
        listFieldComponents.value = lfc;
        fieldObjs.value = fo;
        childsObjs.value = co;
    });

    return {
        actionW: newActionW,
        columns: columnsVals,
        isGroup,
        titleItems,
        scrollX,
        scrollY,
        id,
        listFieldComponents,
        fieldObjs,
        childsObjs,
        onresize,
        expandedRowKeys: Vue.ref([]),
        guid,
    };
}