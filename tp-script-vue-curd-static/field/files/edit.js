define([],function(){
    const fileList=[];
    let fid=0;
    return {
        props:['field','value','validateStatus','info'],
        setup(props,ctx){
            return {
                id:'upload-class-'+window.guid(),
                fileList:Vue.ref([]),
            }
        },
        watch:{
            value:{
                handler(value){
                    if(!value){
                        return [];
                    }
                    let vals=value.split('|');
                    let fileOkObjs={};
                    this.fileList.forEach((v,i)=>{
                        if(v.status==='done'){
                            if(!vals.includes(v.url)){
                                this.fileList.splice(i,1)
                            }else{
                                fileOkObjs[v.url]=v;
                            }
                        }
                    })
                    vals.forEach(v=>{
                        if(fileOkObjs[v]){
                            return;
                        }
                        fid--;
                        const finfo={
                            uid:fid,
                            name:this.getUrlTitle(v),
                            status: 'done',
                            url:v,
                        };
                        if(this.field.multiple){
                            this.fileList.push(finfo)
                        }else{
                            this.fileList=[finfo];
                        }
                    })
                },
                immediate:true,
            }
        },
        computed:{
            acceptTexts(){
                const acceptTexts={};
                for(let i in  this.field.acceptTexts ){
                    acceptTexts[i.toString().toLowerCase()]=this.field.acceptTexts[i];
                }
                return acceptTexts;
            },
        },
        methods:{
            getUrlTitle(url) {
                if(!this.info||!this.info[this.field.name+'InfoArr']||!this.info[this.field.name+'InfoArr'][url]||!this.info[this.field.name+'InfoArr'][url].original_name){
                    let arr=url.split('/');
                    return arr[arr.length-1];
                }
                return this.info[this.field.name+'InfoArr'][url].original_name
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
            responseUrlKey(fileItem){
                return fileItem.response.data.url
            },
            uploadSuccess(fileItem){
                if(fileItem.status==='done'){
                    if(typeof fileItem.response!=='object'||!fileItem.response.code||fileItem.response.code==='0'){
                        fileItem.status='error';
                        this.$message.error({
                            content:'文件[ '+fileItem.name+' ]：'+fileItem.response.msg,
                            duration: 6*1000
                        });
                    }
                }
            },
            change(fileList,fileItem){
                const fileOkObjs = {};
                fileList.forEach(file => {
                    if (file.status === 'done') {
                        fileOkObjs[file.url] = file;
                    }
                });
                const vals = Object.keys(fileOkObjs);
                const val = (vals.length > 1 && !this.field.multiple) ? vals[vals.length - 1] : vals.join('|');
                if(val!==this.value){
                    this.$emit('update:value',val);
                }
            },
        },
        template:`<div class="field-box" :class="[id]">
                    <div class="l">
                        <a-upload
                            :multiple="field.multiple"
                            :action="field.url"
                            :accept="field.accept"
                            list-type="text"
                            v-model:file-list="fileList"
                            :disabled="field.readOnly"
                            :response-url-key="responseUrlKey"
                            with-credentials
                            :download="field.canDown"
                            :show-link="field.canDown"
                            @change="change"
                            @success="uploadSuccess"
                        >
                        </a-upload>
                        <div v-if="field.accept" style="color: #bfbfbf;font-size: 12px;padding-top: 4px">上传文件需为：{{getAcceptText()}}</div>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
        </div>`,
    }
});