define(['vueAdmin'], function (va) {
    let form={},validateStatus={},fieldObjs={};

    vueData.fields.forEach(function(field){
        fieldObjs[field.name]=field;
        form[field.name]=vueData.info&&typeof vueData.info[field.name]!=='undefined'?vueData.info[field.name]:'';
    })

    if(vueData.info&&vueData.info.id){
        form.id=vueData.info.id;
    }


    va({
        data(){
            return {
                loading:false,
                haveGroup:!!vueData.groupFields,
                groupFields:vueData.groupFields||{'':vueData.fields},
                groupGrids:vueData.groupGrids||{},
                labelCol: { span: 4 },
                wrapperCol: { span: 18 },
                form:form,
                info:vueData.info||{},
                fieldHideList:{},
                subBtnTitle:vueData.subBtnTitle||'提交',

            }
        },
        computed:{
            showGroup(){
                if(!this.haveGroup){
                    return false;
                }
                let showGroupNum=0;
                for(let i in this.groupFields){
                    if(this.checkShowGroup(this.groupFields[i])){
                        if(showGroupNum>0){
                            return true;
                        }
                        showGroupNum++;
                    }
                }
                return false;
            },
        },
        watch:{
        },
        methods:{
            onSubmit(option){
                if(window.vueEditOnSubmit){
                    return  window.vueEditOnSubmit.bind(this)(option);
                }
                //我想要子组件可以不关闭当前窗口提交（就是自定义的字段可以新增数据后继续编辑）
                option=option||{};

                this.$refs.pubForm.validate().then(async() => {
                    for(let i in this.groupFields){
                        if(this.$refs['fieldGroup'+i]&&this.$refs['fieldGroup'+i].validateListForm){
                            if(await this.$refs['fieldGroup'+i].validateListForm()===false){
                                return;
                            }
                        }
                    }
                    if(option.showLoading!==false){
                        this.loading=true;
                    }
                    this.$post(vueData.subUrl,this.form).then(async res=>{
                        if(window.thatBtn&&window.thatBtn.refreshPage){
                            if(top.layer&&top.layer.msg){
                                top.layer.msg(res.msg, {icon: 1, shade:  [0.02, '#000'], scrollbar: false, time: 1500, shadeClose: true})
                                (parentWindow||window).vueDefMethods.showLoadMsg('', (parentWindow||window).document.querySelector('body'))
                                (parentWindow||window).setTimeout(()=>{
                                    (parentWindow||window).location.reload();
                                },80)
                            }else{
                                (parentWindow||window).ArcoVue.Message.success(res.msg);
                                (parentWindow||window).vueDefMethods.showLoadMsg('', (parentWindow||window).document.querySelector('#app>.box>.body'))
                                (parentWindow||window).setTimeout(()=>{
                                    (parentWindow||window).location.reload();
                                },200)
                            }
                        }else{
                            (parentWindow||window).ArcoVue.Message.success(res.msg);
                            if(this.form&&this.form.id&&!res.data.refreshList&&(!window.thatBtn||!window.thatBtn.refreshList)){
                                window.listVue.refreshId(this.form.id);
                            }else{
                                window.listVue.refreshTable();
                            }
                        }

                        if(!option.notClose){
                            this.close();
                        }else{
                            //因为数据有其他处理，所以这里返回过来的值就不再赋值到form中了
                            if(!this.form.id){
                                this.form.id=res.data.info.id
                            }
                        }
                        if(option.success){
                            option.success(res)
                        }
                    }).finally(()=>{
                        if(option.showLoading!==false){
                            this.loading=false;
                        }
                        if(option.finally){
                            option.finally()
                        }
                    })
                }).catch(error => {
                    if(error.errorFields&&error.errorFields[0]&&error.errorFields[0].errors&&error.errorFields[0].errors[0]){
                        ArcoVue.Message.warning(error.errorFields[0].errors[0])
                    }else{
                        ArcoVue.Message.warning('请检测是否填写正确')
                    }
                    console.log('error', error);
                });
            },
            close(){
                document.body.dispatchEvent(new Event('closeIframe'));
            },
            checkShowGroup(groupFieldItems){
                for(let i in groupFieldItems){
                    if(groupFieldItems[i].editShow&&!this.fieldHideList[groupFieldItems[i].name]){
                        return true;
                    }
                }
                return false;
            },
        }
    })
});