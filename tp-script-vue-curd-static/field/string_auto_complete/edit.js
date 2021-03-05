define([],function(){
    return {
        props:['field','value','validateStatus'],
        data(){
          return {
              autoCompleteOptions:[],
          }
        },
        methods:{
            onAutoCompleteSearch(event){
                this.autoCompleteSearch(event,this.field.url)
            },
            autoCompleteSearch(val,url){
                this.autoCompleteOptions=[];
                if(!url){
                    return ;
                }
                this.$get(url,{search:val}).then(res=>{
                    let arr=[];
                    res.data.forEach(function(v){
                        arr.push({value:v});
                    })
                    this.autoCompleteOptions=arr;
                })
            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-auto-complete v-model:value="value" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly" :options="autoCompleteOptions" @search="onAutoCompleteSearch"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});