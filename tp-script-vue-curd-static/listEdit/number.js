define([],function(){
    return {
        props:['record','field','list','precision'],
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
                    let val=this.val===null||this.val===undefined?this.record.record[this.field.name]:this.val;
                    if(val===''){
                        return '';
                    }
                    val=this.precision?parseFloat(val):parseInt(val);
                    return isNaN(val)?0:val;
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
                if(this.val===null||this.val===undefined||this.val.toString()===this.record.record[this.field.name].toString()){
                    return;
                }

                this.disabled=true;
                this.$post(this.field.listEdit.saveUrl,{id:this.record.record.id,name:this.field.name,value:this.val}).then(res=>{
                    if(this.field.listEdit.refreshPage==='table'){
                        this.$emit('refresh-table')
                    }else if(this.field.listEdit.refreshPage==='row'){
                        this.$emit('refresh-id',this.record.record.id)
                    }else{
                        this.record.record[this.field.name]=this.val;
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
                        <a-input-number v-model="text" @blur="change" :min="field.min" :max="field.max" :disabled="disabled" :precision="precision||0"></a-input-number>
                    </template>
                    <template v-else><slot></slot></template>
    </span>`,
    }
});