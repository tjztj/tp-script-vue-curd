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
<div style="display: initial">
    <a-popover trigger="click" v-if="record.record[field.name]&&show">
      <template #content>
         <div class="file-box">
            <div class="arco-upload-list arco-upload-list-type-text">
                <div class="arco-upload-list-item arco-upload-list-item-done" v-for="(vo,key) in list" style="margin: 4px 0">
                    <div class="arco-upload-list-item-content">
                        <div class="arco-upload-list-item-name">
                            <span class="arco-upload-list-item-file-icon"><icon-cloud-download /></span>
                            <a class="arco-upload-list-item-name-link" target="_blank" :href="vo" :download="getUrlTitle(vo)">{{getUrlTitle(vo)}}</a>
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