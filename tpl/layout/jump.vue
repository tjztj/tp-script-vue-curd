<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{:tpScriptVueCurdGetHtmlTitle()}</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <link rel="stylesheet" href="/tpscriptvuecurd/arco/arco.min.css?2.44.2" media="all">
    <link rel="stylesheet" href="/tpscriptvuecurd/css/vue.css?v={$vueCurdDebug?time():$vueCurdVersion}" media="all">
    {if !empty($themCssPath)}
    <link rel="stylesheet" href="{$themCssPath}" media="all">
    {/if}
    <script src="/tpscriptvuecurd/vue3/vue.global.prod.js?3.2.47" charset="utf-8"></script>
    <script src="/tpscriptvuecurd/arco/arco-vue.min.js?2.44.2" charset="utf-8"></script>
    <script src="/tpscriptvuecurd/arco/arco-vue-icon.min.js?2.44.2" charset="utf-8"></script>
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
<div id="app-loading" class="app-loading">
    <svg viewBox="25 25 50 50">
        <circle cx="50" cy="50" r="20"></circle>
    </svg>
</div>
<div id="app" style="display: none">
    {__CONTENT__}
</div>
</body>
</html>