define(['vueAdmin'], function (va) {
    let actions = {};

    actions[vueData.vueCurdAction]=function(){

        let form={},validateStatus={},fieldObjs={};

        if(vueData.info&&vueData.info.id){
            form.id=vueData.info.id;
        }
        vueData.fields.forEach(function(field){
            fieldObjs[field.name]=field;
            form[field.name]=vueData.info&&typeof vueData.info[field.name]!=='undefined'?vueData.info[field.name]:'';
        })

        return {
            data() {
                return {
                    loading:false,
                    haveGroup:!!vueData.groupFields,
                    groupFields:vueData.groupFields||{'':vueData.fields},
                    labelCol: { span: 4 },
                    wrapperCol: { span: 18 },
                    form:form,
                    info:vueData.info||{},
                    fieldHideList:{},
                    formLeft:vueData.formLeft,
                    formMaxWidth:vueData.formMaxWidth,
                    groupGrids:vueData.groupGrids||{},
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
                onSubmit(){
                    this.$refs.pubForm.validate().then(async() => {
                        for(let i in this.groupFields){
                            if(this.$refs['fieldGroup'+i]&&this.$refs['fieldGroup'+i].validateListForm){
                                if(await this.$refs['fieldGroup'+i].validateListForm()===false){
                                    return;
                                }
                            }
                        }
                        this.loading=true;
                        this.$post(vueData.saveUrl,this.form).then(async res=>{
                            ArcoVue.Message.success(res.msg);
                        }).finally(()=>{
                            this.loading=false;
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
                checkShowGroup(groupFieldItems){
                    for(let i in groupFieldItems){
                        if(groupFieldItems[i].editShow&&!this.fieldHideList[groupFieldItems[i].name]){
                            return true;
                        }
                    }
                    return false;
                },
                reload(){
                    window.location.reload()
                }
            }
        }
    }



    ///////////////////////////////////////////////////////////////////////////////////////////////
    let return_actions = {};
    if(!actions[window.VUE_CURD.ACTION]&&window.ACTION){
        //方法可以直接写在页面中，不用写在这个js中也可以
        actions[window.VUE_CURD.ACTION]=window.ACTION;
    }
    for (let i in actions) return_actions[i] = function () {
        va(actions[i]())
    }
    return return_actions;
});