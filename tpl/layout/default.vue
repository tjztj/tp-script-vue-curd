<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{:tpScriptVueCurdGetHtmlTitle()}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="/tp-script-vue-curd-static.php?ant-design-vue/antd.min.css?2.0.0-rc.2" media="all">
    <link rel="stylesheet" href="/tp-script-vue-curd-static.php?css/vue.css?v={$vueCurdVersion}" media="all">
    <script>
        window.VUE_CURD={
            GUID: "{$guid}",
            ACTION: "{$vueCurdAction}",
            CONTROLLER: "{$vueCurdController}",
            MODULE:'{$vueCurdModule}',
            VERSION: "{$vueCurdVersion|default='1.0.0'}",
        };
    </script>
</head>
<body>
<div id="app-loading">
    <svg viewBox="25 25 50 50">
        <circle cx="50" cy="50" r="20"></circle>
    </svg>
</div>
<div id="app" style="display: none">
    <a-config-provider :locale="zhCn()">
        {__CONTENT__}
    </a-config-provider>
    <a-modal
        wrap-class-name="body-iframe-modal"
        :destroy-on-close="true"
        v-model:visible="bodyModal.visible"
        :confirm-loading="confirmLoading"
        :footer="null"
        :keyboard="false"
        :mask-closable="false"
        :width="bodyModal.width"
        :height="bodyModal.height"
        :z-index="bodyModal.zIndex"
        :after-close="bodyModal.onclose"
        @ok="handleOk"
    >
        <template #title>
            <div v-html="bodyModal.title"></div>
        </template>
        <iframe scrolling="auto" allowtransparency="true" class="" frameborder="0" :src="bodyModal.url" :onload="bodyModal.onload" :style="{height:bodyModal.height==='auto'?'auto':'calc('+bodyModal.height+' - 56px)'}"></iframe>
    </a-modal>
    <a-drawer
        wrap-class-name="body-iframe-drawer"
        :destroy-on-close="true"
        :mask-closable="false"
        :width="bodyDrawer.width"
        :height="bodyDrawer.height"
        :z-index="bodyDrawer.zIndex"
        :placement="bodyDrawer.placement"
        v-model:visible="bodyDrawer.visible"
        :keyboard="false"
        @close="bodyDrawer.onclose"
    >
        <template #title>
            <div v-html="bodyDrawer.title"></div>
        </template>
        <iframe scrolling="auto" allowtransparency="true" class="" frameborder="0" :src="bodyDrawer.url" :onload="bodyDrawer.onload"></iframe>
    </a-drawer>
    <div style="display: none" id="vue-curd-imgs-show-box">
        <a-image-preview-group>
            <a-image v-for="item in imgShowConfig.list" :src="item" />
        </a-image-preview-group>
    </div>
</div>

<script>window.vueData={$vue_data_json|raw};</script>
<script src="/tp-script-vue-curd-static.php?vue3/vue.global.prod.js?3.0.3" charset="utf-8"></script>
<script src="/tp-script-vue-curd-static.php?moment/moment.min.js" charset="utf-8"></script>
<script>
    moment.locale('zh-cn', {
        months: '一月_二月_三月_四月_五月_六月_七月_八月_九月_十月_十一月_十二月'.split('_'),
        monthsShort: '1月_2月_3月_4月_5月_6月_7月_8月_9月_10月_11月_12月'.split('_'),
        weekdays: '星期日_星期一_星期二_星期三_星期四_星期五_星期六'.split('_'),
        weekdaysShort: '周日_周一_周二_周三_周四_周五_周六'.split('_'),
        weekdaysMin: '日_一_二_三_四_五_六'.split('_'),
        longDateFormat: {
            LT: 'HH:mm',
            LTS: 'HH:mm:ss',
            L: 'YYYY-MM-DD',
            LL: 'YYYY年MM月DD日',
            LLL: 'YYYY年MM月DD日Ah点mm分',
            LLLL: 'YYYY年MM月DD日ddddAh点mm分',
            l: 'YYYY-M-D',
            ll: 'YYYY年M月D日',
            lll: 'YYYY年M月D日 HH:mm',
            llll: 'YYYY年M月D日dddd HH:mm'
        },
        meridiemParse: /凌晨|早上|上午|中午|下午|晚上/,
        meridiemHour: function (hour, meridiem) {
            if (hour === 12) {
                hour = 0;
            }
            if (meridiem === '凌晨' || meridiem === '早上' ||
                meridiem === '上午') {
                return hour;
            } else if (meridiem === '下午' || meridiem === '晚上') {
                return hour + 12;
            } else {
                // '中午'
                return hour >= 11 ? hour : hour + 12;
            }
        },
        meridiem: function (hour, minute, isLower) {
            const hm = hour * 100 + minute;
            if (hm < 600) {
                return '凌晨';
            } else if (hm < 900) {
                return '早上';
            } else if (hm < 1130) {
                return '上午';
            } else if (hm < 1230) {
                return '中午';
            } else if (hm < 1800) {
                return '下午';
            } else {
                return '晚上';
            }
        },
        calendar: {
            sameDay: '[今天]LT',
            nextDay: '[明天]LT',
            nextWeek: '[下]ddddLT',
            lastDay: '[昨天]LT',
            lastWeek: '[上]ddddLT',
            sameElse: 'L'
        },
        dayOfMonthOrdinalParse: /\d{1,2}(日|月|周)/,
        ordinal: function (number, period) {
            switch (period) {
                case 'd':
                case 'D':
                case 'DDD':
                    return number + '日';
                case 'M':
                    return number + '月';
                case 'w':
                case 'W':
                    return number + '周';
                default:
                    return number;
            }
        },
        relativeTime: {
            future: '%s内',
            past: '%s前',
            s: '几秒',
            ss: '%d秒',
            m: '1分钟',
            mm: '%d分钟',
            h: '1小时',
            hh: '%d小时',
            d: '1天',
            dd: '%d天',
            M: '1个月',
            MM: '%d个月',
            y: '1年',
            yy: '%d年'
        },
        week: {
            // GB/T 7408-1994《数据元和交换格式·信息交换·日期和时间表示法》与ISO 8601:1988等效
            dow: 1, // Monday is the first day of the week.
            doy: 4  // The week that contains Jan 4th is the first week of the year.
        }
    })
</script>
<script src="/tp-script-vue-curd-static.php?ant-design-vue/antd.min.js?2.0.0-rc.2" charset="utf-8"></script>
<script src="/tp-script-vue-curd-static.php?require-2.3.6/require.js" charset="utf-8"></script>
<script src="/tp-script-vue-curd-static.php?require-config.js?v={$vueCurdVersion}" charset="utf-8"></script>
<script>
    let jsPath='{$jsPath|default=""}';
    if(jsPath===''){
        jsPath='{:tpScriptVueCurdPublicActionJsPathBase()}/'+window.VUE_CURD.CONTROLLER.replace(/\./g,'/')
            .replace(/(\w)([A-Z])/g,"$1_$2")
            .toLowerCase()+'.js'
    }
    require([jsPath], function (objs) {
        if(objs[VUE_CURD.ACTION]){
            objs[VUE_CURD.ACTION]();
        }
    });
</script>
</body>
</html>