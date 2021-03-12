define([], function () {
    return {
        props: ['field', 'value', 'validateStatus', 'listFieldLabelCol', 'listFieldWrapperCol', 'groupFieldItems'],
        setup(props,ctx){
            const listFieldObjs = {};
            if (props.value&&props.value!=='null') {
                let lists = JSON.parse(props.value);
                for (let n in lists) {
                    listFieldObjs[window.guid()]=lists[n];
                }
            } else {
                listFieldObjs[window.guid()]={};
            }
            return {
                listFieldObjs:Vue.ref(listFieldObjs)
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
                this.listFieldObjs[window.guid()]={};
            },
            removeListField(key) {
                delete this.listFieldObjs[key];
            },
        },


        template: `<div>
                    <div class="list-field-box">
                        <transition-group name="slide-fade">
                            <div class="list-field-box-item-box" v-for="(item,key) in listFieldObjs" :key="key">
                                <a-divider class="list-field-box-item-divider" dashed></a-divider>
                                 <a-form class="list-field-box-item-form" :model="item" :label-col="listFieldLabelCol" :wrapper-col="listFieldWrapperCol" :ref="'listFieldForm'+key">
                                   <div class="list-field-box-remove"><close-outlined class="remove-list-field-box-item-icon" @click="removeListField(key)"></close-outlined></div>
                                   <field-group-item class="list-field-box-item" :group-field-items="field.fields" v-model:form="item"></field-group-item>
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