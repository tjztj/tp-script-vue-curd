define([],function(){
    return {
        props:['field','value','validateStatus'],
        setup(props,ctx){
            let fileList=Vue.ref([]),id='upload-class-'+window.guid();
            if(props.value){
                let imgList=props.value.split('|'),fid=0;

                function getUrlTitle(url) {
                    if(!vueData||!vueData.info||!vueData.info[props.field.name+'InfoArr']||!vueData.info[props.field.name+'InfoArr'][url]||!vueData.info[props.field.name+'InfoArr'][url].original_name){
                        let arr=url.split('/');
                        return arr[arr.length-1];
                    }
                    return vueData.info[props.field.name+'InfoArr'][url].original_name
                }
                fileList.value=imgList.map(function(v){
                    fid--;
                    return {
                        uid:fid,
                        name:getUrlTitle(v),
                        status: 'done',
                        url:v,
                    };
                })
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
                            :accept="field.accept"
                            list-type="picture"
                            :file-list="fileList"
                            :remove="handleRemove"
                            :disabled="field.readOnly"
                            @change="handleChange"
                        >
                        <a-button size="small">
                        <span role="img" aria-label="upload" class="anticon anticon-upload"><svg class="" data-icon="upload" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M400 317.7h73.9V656c0 4.4 3.6 8 8 8h60c4.4 0 8-3.6 8-8V317.7H624c6.7 0 10.4-7.7 6.3-12.9L518.3 163a8 8 0 00-12.6 0l-112 141.7c-4.1 5.3-.4 13 6.3 13zM878 626h-60c-4.4 0-8 3.6-8 8v154H214V634c0-4.4-3.6-8-8-8h-60c-4.4 0-8 3.6-8 8v198c0 17.7 14.3 32 32 32h684c17.7 0 32-14.3 32-32V634c0-4.4-3.6-8-8-8z"></path></svg></span>
                        上传</a-button>
                        </a-upload>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
        </div>`,
    }
});