define([],function(){
    return {
        props:['record','field','list'],
        setup:function (props,ctx){
            const val=Vue.ref(null);
            Vue.watch(()=>props.list[props.record.rowIndex],()=>{
                val.value=null;
            })
            return {
                val,
                disabled:Vue.ref(false)
            }
        },
        computed:{
            text:{
                get(){
                    return this.val===null?this.record.record[this.field.name]:this.val;
                },
                set(val){
                    this.val=val;
                }
            }
        },
        methods:{
            log(...data){
                console.log(...data)
            },
            '$post'(...params){
                return window.vueDefMethods.$post.call(this,...params,)
            },
            change(){
                if(this.val===this.record.record[this.field.name]||this.val===null){
                    return;
                }
                this.disabled=true;
                this.$post(this.field.listEdit.saveUrl,{id:this.record.record.id,name:this.field.name,value:this.val}).then(res=>{
                    if(this.field.listEdit.refreshPage==='table'){
                        this.$emit('refresh-table')
                    }else if(this.field.listEdit.refreshPage==='row'){
                        this.$emit('refresh-id',this.record.record.id)
                    }else{
                        this.record.record['_Original_'+this.field.name]=this.val;
                        // this.list[this.record.rowIndex][this.field.name]=this.val;
                    }
                    this.disabled=false;
                }).catch(()=>{
                    this.disabled=false;
                })
            },
        },
        template:`<span>
                    <template v-if="field.listEdit&&field.listEdit.saveUrl">
                        <a-input v-model="text" @blur="change" :disabled="disabled"></a-input>
                    </template>
                    <template v-else><slot></slot></template>
    </span>`,
    }
});