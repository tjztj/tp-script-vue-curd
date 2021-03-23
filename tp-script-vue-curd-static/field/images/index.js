define([],function(){
    return {
        props:['record','field'],
        setup(props,ctx){
            const show=Vue.ref(true);


            if(props.field.removeMissings&&props.record.text){
                let fileList=props.record.text.split('|');
                //删掉丢失的图片
                setTimeout(()=>{
                    for(let i in fileList){
                        let ImgObj=new Image();
                        ImgObj.onerror=()=>{
                            fileList=fileList.filter(v=>{
                                return v!==fileList[i]
                            })
                            if(fileList.length===0){
                                show.value=false;
                            }
                            console.log(fileList);
                        }
                        ImgObj.src= fileList[i];
                    }
                },1)
            }
            return {
                show
            }
        },
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <a-tooltip placement="topLeft" v-if="record.text&&show">
                        <template #title>查看图片</template>
                        <a @click="showImages(record.text)"><file-image-outlined></file-image-outlined> 查看</a>
                    </a-tooltip>
                    <span v-else style="color: #f0f0f0">无</span>
                </div>`,
    }
});