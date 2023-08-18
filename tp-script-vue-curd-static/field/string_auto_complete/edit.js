define([],function(){
    return {
        props:['field','value','validateStatus'],
        data(){
          return {
              autoCompleteOptions:[],
          }
        },
        computed:{
            val:{
                get(){
                    return this.value
                },
                set(val){
                    this.$emit('update:value', val);
                }
            }
        },
        mounted(){
            this.autoCompleteOptions=this.field.items.map(v=>{
                return {value:v}
            });
            if(this.field.beginGetOptions&&(this.value===''||typeof this.value==='undefined')){
                this.onAutoCompleteSearch('');
            }
        },
        methods:{
            '$get'(url, params){
                if (window.VUE_CURD.MODULE&&url.indexOf('/' + window.VUE_CURD.MODULE + '/') !== 0&&/^\/?\w+\.php/.test(url)===false&&/^https?:/.test(url)===false) {
                    url = '/' + window.VUE_CURD.MODULE + '/'+url;
                }
                return service({url, method: 'get',params,headers:{'X-REQUESTED-WITH':'xmlhttprequest'}})
            },
            onAutoCompleteSearch(event){
                this.autoCompleteSearch(event,this.field.url)
            },
            autoCompleteSearch(val,url){
                if(url){
                    this.autoCompleteOptions=[];
                    let arr=this.field.items.map(v=>{
                        return {value:v}
                    });
                    this.$get(url,{search:val}).then(res=>{
                        res.data.forEach(function(v){
                            arr.push({value:v});
                        })
                        this.autoCompleteOptions=arr;
                    })
                }

            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-auto-complete v-model="val" :placeholder="field.placeholder||'请填写'+field.title" :disabled="field.readOnly" :data="autoCompleteOptions" @search="onAutoCompleteSearch" :filter-option="field.willFilter"/>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});