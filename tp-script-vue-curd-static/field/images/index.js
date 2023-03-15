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
            imgs(){
                if(!this.record.record[this.field.name]){
                    return [];
                }
                return this.record.record[this.field.name].split('|').filter(v=>v)||[];
            }
        },
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <template v-if="record.record[field.name]&&show">
                        <a-image-preview-group infinite v-if="field.listShowImg.show&&imgs.length>0">
                           <div style="display: flex;align-items: center">
                               <a-image v-for="item in imgs" style="flex: 1" :key="item" class="list-img-field-box" :src="item" :style="{'max-width': field.listShowImg.maxWidth,'max-height': field.listShowImg.maxHeight}"></a-image>
                            </div>
                        </a-image-preview-group>
                        <a v-else @click="showImages(record.record[field.name])"><icon-file-image /> 查看</a>
                    </template>
                    <span v-else style="color: #d9d9d9">无</span>
                </div>`,
    }
});