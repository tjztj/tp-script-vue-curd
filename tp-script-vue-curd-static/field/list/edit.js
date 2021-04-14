define([], function () {
    return {
        props: ['field', 'value', 'validateStatus', 'listFieldLabelCol', 'listFieldWrapperCol', 'groupFieldItems','fieldHideList'],
        setup(props,ctx){
            const listFieldObjs = {},currentFieldHideList={};
            if (props.value&&props.value!=='null') {
                let lists = JSON.parse(props.value);
                for (let n in lists) {
                    currentFieldHideList[n]={};
                    listFieldObjs[n]=lists[n];
                }
            } else {
                const guid=window.guid();
                currentFieldHideList[guid]={};
                listFieldObjs[guid]={};
            }
            return {
                listFieldObjs:Vue.ref(listFieldObjs),
                currentFieldHideList:Vue.ref(currentFieldHideList),
            }
        },
        watch: {
            listFieldObjs:{
                handler(listFieldObjs) {
                    this.$emit('update:value', JSON.stringify(listFieldObjs));
                },
                deep: true,
                immediate: true,
            }
        },
        methods: {
            addListField() {
                const guid=window.guid();
                this.currentFieldHideList[guid]={};
                this.listFieldObjs[guid]={};
            },
            removeListField(key) {
                delete this.listFieldObjs[key];
                delete this.currentFieldHideList[key];
            },
        },


        template: `<div>
                    <div class="list-field-box">
                        <transition-group name="slide-fade">
                            <div class="list-field-box-item-box" v-for="(item,key) in listFieldObjs" :key="key">
                                <a-divider class="list-field-box-item-divider" dashed></a-divider>
                                 <a-form class="list-field-box-item-form" :model="item" :label-col="listFieldLabelCol" :wrapper-col="listFieldWrapperCol" :ref="'listFieldForm'+key">
                                   <div class="list-field-box-remove"><close-outlined class="remove-list-field-box-item-icon" @click="removeListField(key)"></close-outlined></div>
                                   <field-group-item class="list-field-box-item" :group-field-items="field.fields" v-model:form="item" v-model:field-hide-list="currentFieldHideList[key]"></field-group-item>
                                 </a-form>
                            </div>
                        </transition-group>
                    </div>
                    <div class="list-field-add-btn-box" v-if="!field.readOnly">
                         <plus-outlined class="add-list-field-box-item-icon" @click="addListField"></plus-outlined>                                        
                    </div>
                </div>`,
    }
});