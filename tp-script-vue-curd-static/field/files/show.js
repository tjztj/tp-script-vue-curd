define([], function () {
    return {
        props: ['info', 'field'],
        data() {
            return {
                errorUrls: {}
            }
        },
        computed:{
            list(){
                if(this.info[this.field.name+'Arr']){
                    return this.info[this.field.name+'Arr'];
                }
                let val=this.info[this.field.name]||'';
                if(!val){
                    return [];
                }
                if(typeof val==='object'){
                    return val;
                }
                return val.split('|')
            },
        },
        methods: {
            getUrlTitle(url) {
                if(!this.info[this.field.name+'InfoArr']||!this.info[this.field.name+'InfoArr'][url]||!this.info[this.field.name+'InfoArr'][url].original_name){
                    let arr=url.split('/');
                    return arr[arr.length-1];
                }
                return this.info[this.field.name+'InfoArr'][url].original_name
            },
        },
        template: `<div>
                    <div class="file-box">
                        <div class="arco-upload-list arco-upload-list-type-text">
                            <div class="arco-upload-list-item arco-upload-list-item-done" v-for="(vo,key) in list" style="margin: 4px 0">
                                <div class="arco-upload-list-item-content" style="border: 1px solid #fff">
                                    <div class="arco-upload-list-item-name">
                                        <span class="arco-upload-list-item-file-icon"><icon-cloud-download /></span>
                                        <a v-if="field.canDown" class="arco-upload-list-item-name-link" target="_blank" :href="vo" :download="getUrlTitle(vo)">{{getUrlTitle(vo)}}</a>
                                        <a v-else class="arco-upload-list-item-name-link">{{getUrlTitle(vo)}}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});