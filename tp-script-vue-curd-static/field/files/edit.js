define([],function(){
    const isAccOk={};
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
            const acceptTexts={};
            for(let i in  props.field.acceptTexts ){
                acceptTexts[i.toString().toLowerCase()]=props.field.acceptTexts[i];
            }

            return {
                fileList,
                id,
                acceptTexts,
                errTitles:Vue.ref([]),
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
                let urls=[],accErrs=[];
                this.fileList =data.fileList.map((file)=>{
                    if(file.status==='done'){
                        if(file.response){
                            if(file.response.code==0){
                                antd.message.error('文件[ '+file.name+' ]：'+file.response.msg,6);
                            }else{
                                file.url=file.response.data.url
                            }
                        }
                    }else if((!file.status||file.status==='error')&&isAccOk[file.uid]===false){
                        accErrs.push(file);
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
                if(accErrs.length===0){
                    this.$emit('update:validateStatus',value?'success':'error');
                }else{
                    this.$emit('update:validateStatus','error');
                    let errTitles=[];
                    accErrs.forEach(v=>{
                        v.status='error'
                        if(!errTitles.includes(v.name)){
                            errTitles.push(v.name)
                        }
                    })
                    this.errTitles=errTitles;
                }
            },
            getAcceptText(){
                let arr=this.field.accept.split(',');
                let returns=[];
                for(let i in arr){
                    arr[i]=arr[i].trim().toLowerCase();
                    let fileText=this.acceptTexts[arr[i]]?(this.acceptTexts[arr[i]]+'文件'):arr[i];
                    if(!returns.includes(fileText)){
                        returns.push(fileText);
                    }
                }
                return returns.join('、');
            },
            beforeUpload(file, fileList){
                if(!this.field.accept){
                    isAccOk[file.uid]=true;
                    return;
                }
                const accepts=this.field.accept.split(',');
                for(let i in accepts){
                    accepts[i]=accepts[i].trim();
                    if(accepts[i].indexOf('.')===0){
                        if(accepts[i].toLowerCase()===file.name.substring(file.name.lastIndexOf(".")).toLowerCase()){
                            isAccOk[file.uid]=true;
                            return;
                        }
                    }else{
                        if(accepts[i].toLowerCase()===file.type.toLowerCase()){
                            isAccOk[file.uid]=true;
                            return;
                        }

                    }
                }
                isAccOk[file.uid]=false;
                return false;
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
                            :before-upload="beforeUpload"
                            @change="handleChange"
                        >
                        <a-button size="small">
                        <span role="img" aria-label="upload" class="anticon anticon-upload"><svg class="" data-icon="upload" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M400 317.7h73.9V656c0 4.4 3.6 8 8 8h60c4.4 0 8-3.6 8-8V317.7H624c6.7 0 10.4-7.7 6.3-12.9L518.3 163a8 8 0 00-12.6 0l-112 141.7c-4.1 5.3-.4 13 6.3 13zM878 626h-60c-4.4 0-8 3.6-8 8v154H214V634c0-4.4-3.6-8-8-8h-60c-4.4 0-8 3.6-8 8v198c0 17.7 14.3 32 32 32h684c17.7 0 32-14.3 32-32V634c0-4.4-3.6-8-8-8z"></path></svg></span>
                        上传</a-button>
                        </a-upload>
                        <div v-if="errTitles.length>0" style="color: #faad14;font-size: 14px;line-height: 1.5715;margin-top: -1px;min-height: 23px;margin-bottom: -1px;">
                            <b style="color: #bfbfbf;padding-right: 3px">「</b>
                            <span v-for="(item,index) in errTitles">
                            <a-divider type="vertical" style="background-color: #bfbfbf" v-if="index>0"></a-divider>
                            {{item}}
                            </span>
                            <b style="color: #bfbfbf;padding:0 4px">」</b>文件不符合上传要求，将不会上传
                        </div>
                        <div v-if="field.accept" style="color: #bfbfbf;font-size: 12px">上传文件需为：{{getAcceptText()}}</div>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
        </div>`,
    }
});