define([], function () {
    return {
        props: ['field', 'value', 'validateStatus'],
        setup(props,ctx){
            let data = {};
            if(props.value||props.value==='0'||props.value===0){
                props.value.toString().split(props.field.separate).forEach(v => {
                    data[window.guid()] = v;
                })
            }else{
                data[window.guid()]='';
            }
            return {
                val:Vue.ref(data),
                autoCompleteOptions: Vue.ref({}),
            }
        },
        watch: {
            val:{
                handler(val) {
                    const value=[];
                    for(let i in val){
                        value.push(val[i])
                    }
                    this.$emit('update:value', value.join(this.field.separate));
                },
                deep: true,
                immediate: true,
            }
        },
        methods: {
            '$get'(url, params){
                if (window.VUE_CURD.MODULE&&url.indexOf('/' + window.VUE_CURD.MODULE + '/') !== 0&&/^\/?\w+\.php/.test(url)===false&&/^https?:/.test(url)===false) {
                    url = '/' + window.VUE_CURD.MODULE + '/'+url;
                }
                return service({url, method: 'get',params,headers:{'X-REQUESTED-WITH':'xmlhttprequest'}})
            },
            addMoreString() {
                const key=window.guid();

                this.val[key] = '';
                if(this.field.beginGetOptions){
                    this.onAutoCompleteSearchMoreString('',key);
                }
            },
            removeMoreString(key) {
                delete this.val[key];
            },
            autoCompleteSearch(val, key) {
                this.autoCompleteOptions[key] = [];
                if (!this.field.url) {
                    return;
                }

                this.$get(this.field.url, {search: val}).then(res => {
                    let arr = [];
                    res.data.forEach(function (v) {
                        arr.push({value: v});
                    })
                    this.autoCompleteOptions[key] = arr;
                })
            },
            onAutoCompleteSearchMoreString(event, key) {
                this.autoCompleteSearch(event, key)
            }
        },
        template: `<div>
                    <div class="inputs-box">
                      <transition-group name="slide-fade">
                        <div class="inputs-box-item" v-for="(item,key) in val" :key="key">
                            <template v-if="field.url">
                                <div class="field-box">
                                    <div class="l">
                                        <div class="more-string-auto-complete-row">
                                            <div class="more-string-auto-complete-input">
                                                <a-auto-complete v-model:value="val[key]" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly" :options="autoCompleteOptions[key]" :default-open="field.beginGetOptions" @search="onAutoCompleteSearchMoreString($event,key)"/>
                                            </div>
                                            <div class="more-string-auto-complete-rm" @click="removeMoreString(key)"><close-outlined class="remove-inputs-box-item-icon"></close-outlined></div>
                                        </div>
                                    </div>
                                    <div class="r">
                                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <a-input v-model:value="val[key]" :placeholder="field.placeholder||'请填写'+field.title" :suffix="field.ext" :disabled="field.readOnly">
                                <template v-if="!field.readOnly" #addonAfter><close-outlined class="remove-inputs-box-item-icon" @click="removeMoreString(key)"></close-outlined></template>
                                </a-input>
                            </template>
                            
                        </div>
                        </transition-group>
                    </div>
                    <div class="inputs-add-btn-box" v-if="!field.readOnly">
                         <plus-outlined class="add-inputs-box-item-icon" @click="addMoreString(field)"></plus-outlined>                                        
                    </div>
                </div>`,
    }
});