define([],function(){
    return {
        props:['record','field'],
        computed:{
            checked:{
                get(){
                    return this.getVal();
                },
                set(val){
                    if(this.field.readOnly||this.field.indexChangeUrl===''){
                        //不能修改
                        return;
                    }
                    this.$post(this.field.indexChangeUrl,{
                        [this.field.name]:val?this.field.items[1].value:this.field.items[0].value
                    }).then(res=>{
                        this.$emit('refresh-table')
                    })
                }
            }
        },
        methods:{
            getVal(){
                const val=this.record[this.field.name].toString();
                return val===this.field.items[1].value.toString()||val===this.field.items[1].title.toString();
            },
            '$post'(url, data) {
                if (url.indexOf('/' + window.VUE_CURD.MODULE + '/') === 0) {
                    url = url.replace('\/' + window.VUE_CURD.MODULE + '\/', '')
                }
                return service({
                    url,
                    method: 'post',
                    data: Qs.stringify(data),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-REQUESTED-WITH': 'xmlhttprequest'
                    }
                })
            },
        },
        template:`<div>
                     <a-switch v-model:checked="checked" :checked-children="field.items[1].title" :un-checked-children="field.items[0].title" :disabled="field.readOnly||field.indexChangeUrl===''"/>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});