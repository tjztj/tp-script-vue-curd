define([],function(){
    return {
        props:['record','field'],
        setup(props,ctx){
            const show=Vue.ref(true);
            return {
                show
            }
        },
        computed:{
          list(){
              if(this.record.record[this.field.name+'Arr']){
                  return this.record.record[this.field.name+'Arr'];
              }
              let val=this.record.record[this.field.name]||'';
              if(!val){
                  return [];
              }
              if(typeof val==='object'){
                  return val;
              }
              return val.split('|')
          },
        },
        methods:{
            getUrlTitle(url) {
                if(!this.record.record[this.field.name+'InfoArr']||!this.record.record[this.field.name+'InfoArr'][url]||!this.record.record[this.field.name+'InfoArr'][url].original_name){
                    let arr=url.split('/');
                    return arr[arr.length-1];
                }
                return this.record.record[this.field.name+'InfoArr'][url].original_name
            }
        },
        template:`
<div>
    <a-popover trigger="click" v-if="record.text&&show">
      <template #content>
         <div class="file-box">
            <div class="ant-upload-list ant-upload-list-text">
                <div v-for="(vo,key) in list">
                    <div class="ant-upload-list-item ant-upload-list-item-done ant-upload-list-item-list-type-text">
                        <div class="ant-upload-list-item-info">
                            <span>
                                <span role="img" aria-label="paper-clip" class="anticon anticon-paper-clip"><svg class="" data-icon="paper-clip" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M779.3 196.6c-94.2-94.2-247.6-94.2-341.7 0l-261 260.8c-1.7 1.7-2.6 4-2.6 6.4s.9 4.7 2.6 6.4l36.9 36.9a9 9 0 0012.7 0l261-260.8c32.4-32.4 75.5-50.2 121.3-50.2s88.9 17.8 121.2 50.2c32.4 32.4 50.2 75.5 50.2 121.2 0 45.8-17.8 88.8-50.2 121.2l-266 265.9-43.1 43.1c-40.3 40.3-105.8 40.3-146.1 0-19.5-19.5-30.2-45.4-30.2-73s10.7-53.5 30.2-73l263.9-263.8c6.7-6.6 15.5-10.3 24.9-10.3h.1c9.4 0 18.1 3.7 24.7 10.3 6.7 6.7 10.3 15.5 10.3 24.9 0 9.3-3.7 18.1-10.3 24.7L372.4 653c-1.7 1.7-2.6 4-2.6 6.4s.9 4.7 2.6 6.4l36.9 36.9a9 9 0 0012.7 0l215.6-215.6c19.9-19.9 30.8-46.3 30.8-74.4s-11-54.6-30.8-74.4c-41.1-41.1-107.9-41-149 0L463 364 224.8 602.1A172.22 172.22 0 00174 724.8c0 46.3 18.1 89.8 50.8 122.5 33.9 33.8 78.3 50.7 122.7 50.7 44.4 0 88.8-16.9 122.6-50.7l309.2-309C824.8 492.7 850 432 850 367.5c.1-64.6-25.1-125.3-70.7-170.9z"></path></svg></span>
                                <a target="_blank" rel="noopener noreferrer" class="ant-upload-list-item-name ant-upload-list-item-name-icon-count-1" title="查看/下载" :href="vo">{{getUrlTitle(vo)}}</a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </template>
      <a>查看</a>
    </a-popover>
  <span v-else style="color: #d9d9d9">无</span>
</div>`,
    }
});