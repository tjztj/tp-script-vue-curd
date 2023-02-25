define([], function () {
    let lastVal=null;
    return {
        props: ['field', 'value', 'validateStatus', 'listFieldLabelCol', 'listFieldWrapperCol', 'groupFieldItems','fieldHideList'],
        setup(props,ctx){

            return {

            }
        },
        data(){
          return {
              listFieldObjs:{},
              currentFieldHideList:{},
          }
        },
        watch: {
            listFieldObjs:{
                handler(listFieldObjs) {
                    lastVal= JSON.stringify(listFieldObjs);
                    this.$emit('update:value', lastVal);
                },
                deep: true,
                immediate: true,
            },
            value(value){
                if(lastVal===value){
                    return;
                }
                this.listFieldObjs={};
                this.currentFieldHideList={};
                this.$nextTick(()=>{
                    setTimeout(()=>{
                        this.setVal(value);
                        this.$forceUpdate();
                    })
                })

            },
        },
        computed:{
          fields(){
              return this.field.fields.map(v=>{
                  v.editLabelAlign='left'
                  return v;
              })
          }
        },
        beforeMount(){
            this.setVal(this.value)
        },
        methods: {
            setVal(value){
                const listFieldObjs = {},currentFieldHideList={};
                if (value&&value!=='null'&&value!=='{}') {
                    let lists = JSON.parse(value);
                    for (let n in lists) {
                        currentFieldHideList[n]={};
                        listFieldObjs[n]=lists[n];
                    }
                } else {
                    const guid=window.guid();
                    currentFieldHideList[guid]={};
                    listFieldObjs[guid]={};
                }
                this.listFieldObjs=listFieldObjs;
                this.currentFieldHideList=currentFieldHideList;
            },
            addListField() {
                const guid=window.guid();
                this.currentFieldHideList[guid]={};
                this.listFieldObjs[guid]={};
                this.$forceUpdate();
            },
            removeListField(key) {
                delete this.listFieldObjs[key];
                delete this.currentFieldHideList[key];
                this.$forceUpdate();
            },
        },


        template: `<div>
                    <div class="list-field-box">
                        <transition-group name="slide-fade">
                            <div class="list-field-box-item-box" v-for="(item,key) in listFieldObjs" :key="key">
                                <a-divider class="list-field-box-item-divider" style="border-bottom-style: dashed" />
                                 <a-form class="list-field-box-item-form" :model="item" :label-col-props="listFieldLabelCol" :wrapper-col-props="listFieldWrapperCol" :ref="'listFieldForm'+key">
                                   <div class="list-field-box-remove"><icon-close class="remove-list-field-box-item-icon" @click="removeListField(key)" /></div>
                                   <field-group-item class="list-field-box-item" :group-field-items="fields" :info="item" v-model:form="item" v-model:field-hide-list="currentFieldHideList[key]"></field-group-item>
                                 </a-form>
                            </div>
                        </transition-group>
                    </div>
                    <div class="list-field-add-btn-box" v-if="!field.readOnly">
                        <icon-plus class="add-list-field-box-item-icon" @click="addListField" />              
                    </div>
                </div>`,
    }
});