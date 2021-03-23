define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            let fileList=Vue.ref([]),id='upload-class-'+window.guid();
            if(props.value){
                let imgList=props.value.split('|'),fid=0;
                fileList.value=imgList.map(function(v){
                    fid--;
                    return {
                        uid:fid,
                        name:v.substring(v.lastIndexOf("/")+1,v.length),
                        status: 'done',
                        url:v,
                    };
                })

                if(props.field.removeMissings){
                    //删掉丢失的图片
                    setTimeout(()=>{
                        for(let i in fileList.value){
                            let ImgObj=new Image();
                            ImgObj.onerror=()=>{
                                fileList.value=fileList.value.filter(v=>{
                                    return v.url!==fileList.value[i].url
                                })
                            }
                            ImgObj.src= fileList.value[i].url;
                        }
                    },1)
                }
            }
            return {
                fileList,
                id
            }
        },
        methods:{
            handleRemove(){
                return file => {
                    if(file.url){
                        this.$emit('update:value',this.value.split('|').filter(url=>url&&url!==file.url).join('|'));
                    }
                }
            },
            handlePreview(file) {
                const images=this.fileList.filter(function(vo){
                    return vo.url?true:false;
                }).map(function(vo){
                    return vo.url
                });
                window.top.showImages(images,images.indexOf(file.url))
            },
            handleChange(data,field) {
                let urls=[];
                this.fileList =data.fileList.map(function(file){
                    if(file.status==='done'){
                        if(file.response){
                            if(file.response.code==0){
                                antd.message.error('文件[ '+file.name+' ]：'+file.response.msg,6);
                            }else{
                                file.url=file.response.data.url
                            }
                        }
                    }
                    return file;
                }).filter(function(file){
                    if(file.status==='done'){
                        if(urls.indexOf(file.url)!==-1||!file.url){
                            return false;
                        }
                        urls.push(file.url)
                    }
                    return true;
                });
                let value=urls.join('|');
                this.$emit('update:value',value);
                this.$emit('update:validateStatus',value?'success':'error');

            },
        },
        template:`<div class="field-box" :class="[id]">
                    <div class="l">
                        <a-upload
                            multiple
                            :action="field.url"
                            accept="image/*"
                            list-type="picture-card"
                            :file-list="fileList"
                            :remove="handleRemove"
                            :disabled="field.readOnly"
                            @preview="handlePreview"
                            @change="handleChange"
                        >
                            <plus-outlined />
                        </a-upload>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
        </div>`,
    }
});