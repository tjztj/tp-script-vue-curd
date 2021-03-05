define([],function(){
    return {
        props:['field','value','validateStatus'],
        data(){
          return {
              autoCompleteOptions:{},
          }
        },
        methods:{
            setMoreStringToArr(){
                if(typeof this.value!=='object'){
                    let data={};
                    if(this.value){
                        this.value.split(this.field.separate).forEach(v=>{
                            data[window.guid()]=v;
                        })
                    }else{
                        data[window.guid()]='';
                    }
                    this.value=data;
                }
            },
            addMoreString(){
                this.setMoreStringToArr();
                //todo
                this.$emit('update:value',);
                this.value[window.guid()]='';
            },
            removeMoreString(key){
                this.setMoreStringToArr();
                //todo
                delete this.value[key];
            },
            autoCompleteSearch(val,key){
                this.autoCompleteOptions[key]=[];
                if(!this.field.url){
                    return ;
                }

                this.$get(this.field.url,{search:val}).then(res=>{
                    let arr=[];
                    res.data.forEach(function(v){
                        arr.push({value:v});
                    })
                    this.autoCompleteOptions[key]=arr;
                })
            },
            onAutoCompleteSearchMoreString(event,key){
                this.autoCompleteSearch(event,key)
            }
        },
        template:`<div>
                    <div class="inputs-box" :data-isinit="setMoreStringToArr(field)">
                      <transition-group name="slide-fade">
                        <div class="inputs-box-item" v-for="(item,key) in value" :key="key">
                            <template v-if="field.url">
                                <div class="field-box">
                                    <div class="l">
                                        <div class="more-string-auto-complete-row">
                                            <div class="more-string-auto-complete-input">
                                                <a-auto-complete v-model:value="value[key]" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly" :options="autoCompleteOptions[key]" @search="onAutoCompleteSearchMoreString($event,key)"/>
                                            </div>
                                            <div class="more-string-auto-complete-rm" @click="removeMoreString(field,key)"><close-outlined class="remove-inputs-box-item-icon"></close-outlined></div>
                                        </div>
                                    </div>
                                    <div class="r">
                                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                                    </div>
                                </div>
                            </template>
                            <template v-else>
                                <a-input v-model:value="value[key]" :placeholder="field.placeholder||'请填写'+field.title" :suffix="field.ext" :disabled="field.readOnly">
                                <template v-if="!field.readOnly" #addonAfter><close-outlined class="remove-inputs-box-item-icon" @click="removeMoreString(field,key)"></close-outlined></template>
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