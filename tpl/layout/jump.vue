<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{:tpScriptVueCurdGetHtmlTitle()}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="/tp-script-vue-curd-static.php?ant-design-vue/antd.min.css?2.2.7" media="all">
    <link rel="stylesheet" href="/tp-script-vue-curd-static.php?css/vue.css?v={$vueCurdDebug?time():$vueCurdVersion}" media="all">
    <script src="/tp-script-vue-curd-static.php?vue3/vue.global.prod.js?3.2.11" charset="utf-8"></script>
    <script src="/tp-script-vue-curd-static.php?moment/moment.min.js" charset="utf-8"></script>
    <script>
        moment.locale("zh-cn",{months:"一月_二月_三月_四月_五月_六月_七月_八月_九月_十月_十一月_十二月".split("_"),monthsShort:"1月_2月_3月_4月_5月_6月_7月_8月_9月_10月_11月_12月".split("_"),weekdays:"星期日_星期一_星期二_星期三_星期四_星期五_星期六".split("_"),weekdaysShort:"周日_周一_周二_周三_周四_周五_周六".split("_"),weekdaysMin:"日_一_二_三_四_五_六".split("_"),longDateFormat:{LT:"HH:mm",LTS:"HH:mm:ss",L:"YYYY-MM-DD",LL:"YYYY年MM月DD日",LLL:"YYYY年MM月DD日Ah点mm分",LLLL:"YYYY年MM月DD日ddddAh点mm分",l:"YYYY-M-D",ll:"YYYY年M月D日",lll:"YYYY年M月D日 HH:mm",llll:"YYYY年M月D日dddd HH:mm"},meridiemParse:/凌晨|早上|上午|中午|下午|晚上/,meridiemHour:function(a,b){return 12===a&&(a=0),"凌晨"===b||"早上"===b||"上午"===b?a:"下午"===b||"晚上"===b?a+12:a>=11?a:a+12},meridiem:function(a,b){const d=100*a+b;return 600>d?"凌晨":900>d?"早上":1130>d?"上午":1230>d?"中午":1800>d?"下午":"晚上"},calendar:{sameDay:"[今天]LT",nextDay:"[明天]LT",nextWeek:"[下]ddddLT",lastDay:"[昨天]LT",lastWeek:"[上]ddddLT",sameElse:"L"},dayOfMonthOrdinalParse:/\d{1,2}(日|月|周)/,ordinal:function(a,b){switch(b){case"d":case"D":case"DDD":return a+"日";case"M":return a+"月";case"w":case"W":return a+"周";default:return a}},relativeTime:{future:"%s内",past:"%s前",s:"几秒",ss:"%d秒",m:"1分钟",mm:"%d分钟",h:"1小时",hh:"%d小时",d:"1天",dd:"%d天",M:"1个月",MM:"%d个月",y:"1年",yy:"%d年"},week:{dow:1,doy:4}});
    </script>
    <script src="/tp-script-vue-curd-static.php?ant-design-vue/antd.min.js?2.2.7" charset="utf-8"></script>
    <script>
        window.VUE_CURD={
            GUID: "{$guid}",
            ACTION: "{$vueCurdAction}",
            CONTROLLER: "{$vueCurdController}",
            MODULE:'{$vueCurdModule}',
            VERSION: "{$vueCurdVersion|default='1.0.0'}",
            SITE_VERSION:'{$version|default=\'\'}',
            DEBUG:'{$vueCurdDebug?1:0}'==='1'
        };
    </script>
    <script>window.vueData={$vue_data_json|raw};</script>
</head>
<body>
<div id="app-loading">
    <svg viewBox="25 25 50 50">
        <circle cx="50" cy="50" r="20"></circle>
    </svg>
</div>
<div id="app" style="display: none">
    {__CONTENT__}
</div>
</body>
</html>