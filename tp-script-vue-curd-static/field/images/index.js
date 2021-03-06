define([],function(){
    return {
        props:['record','field'],
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <a-tooltip placement="topLeft" v-if="record.text">
                        <template #title>查看图片</template>
                        <a @click="showImages(record.text)"><file-image-outlined></file-image-outlined> 查看</a>
                    </a-tooltip>
                    <span v-else style="color: #f0f0f0">无</span>
                </div>`,
    }
});