define([],function(){

    return {
        props: ['field', 'value', 'validateStatus', 'form'],
        template:`
        <div>
          <a-divider v-if="field.topHrText!=''">{{field.topHrText}}</a-divider>
        <a-spin tip="加载中..." :loading="loading" style="display: block">
        <iframe v-if="url" :src="url" :onload="iframeLoad" width="100%" :height="height+'px'" frameborder="0" :id="iframeId" class="url_page-iframe"></iframe>
        </a-spin>
        <a-divider v-if="field.bottomHrText!=''">{{field.bottomHrText}}</a-divider>
</div>`,
    }
});