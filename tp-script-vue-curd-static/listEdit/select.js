define([],function(){
    return {
        props:['record','field','list','multiple'],
        setup:function (props,ctx){
            // const val=Vue.ref(null);
            // Vue.watch(()=>props.list[props.record.rowIndex],()=>{
            //     val.value=null;
            // })

            const filterOption = (input, option) => {
                return option.title.toLowerCase().indexOf(input.toLowerCase()) >= 0;
            };

            return {
                disabled:Vue.ref(false),
                filterOption
            }
        },
        computed:{
            value(){
                return this.record.record['_Original_'+this.field.name];
            },
            val:{
                get(){
                    const nullValue=typeof this.field.nullVal==='string'||typeof this.field.nullVal==='number'?this.field.nullVal.toString():null;

                    if(this.multiple){
                        if(this.value===''){
                            return [];
                        }
                        if(typeof this.value==='number'&&(nullValue===null||this.value.toString()!==nullValue)){
                            return this.value.toString();
                        }
                        if(typeof this.value==='string'&&(nullValue===null||this.value!==nullValue)){
                            return this.value.split(',');
                        }
                        return [];
                    }
                    return typeof this.value==='undefined'||this.value===''||(nullValue!==null&&this.value.toString()===nullValue)?undefined:this.value.toString();
                },
                set(val){
                    if(val===undefined){
                        this.change('');
                        return;
                    }
                    this.change( typeof val==='object'?val.join(','):val);
                }
            },
            option(){
                let groupItems={'':[]};
                let haveGroup=false;
                this.field.items.forEach(v=>{
                    if(v.showItem===false||v.hide){
                        return;
                    }
                    if(v.group){
                        haveGroup=true;
                    }else{
                        v.group='';
                    }
                    if(!groupItems[v.group]){
                        groupItems[v.group]=[];
                    }
                    v.label=v.title||v.text;
                    groupItems[v.group].push(v);
                });

                if(!haveGroup){
                    return groupItems[''];
                }

                let optionGroups=[];
                for(let n in groupItems){
                    if(n){
                        optionGroups.push(...groupItems[n]);
                    }else{
                        optionGroups.push({
                            isGroup:true,
                            label:n,
                            options:groupItems[n],
                        })
                    }
                }
                return optionGroups;
            },
        },
        methods:{
            log(...data){
                console.log(...data)
            },
            '$post'(...params){
                return window.vueDefMethods.$post.call(this,...params,)
            },
            change(val){
                if(val===this.record.record['_Original_'+this.field.name]||val===null){
                    return;
                }
                this.disabled=true;
                this.$post(this.field.listEdit.saveUrl,{id:this.record.record.id,name:this.field.name,value:val}).then(res=>{
                    if(this.field.listEdit.refreshPage==='table'){
                        this.$emit('refresh-table')
                    }else if(this.field.listEdit.refreshPage==='row'){
                        this.$emit('refresh-id',this.record.record.id)
                    }else{
                        this.record.record['_Original_'+this.field.name]=this.val;
                        // this.list[this.record.rowIndex]['_Original_'+this.field.name]=val;
                    }
                    this.disabled=false;
                }).catch(()=>{
                    this.disabled=false;
                })
            },
        },
        template:`<span>
                    <template v-if="field.listEdit&&field.listEdit.saveUrl">
                        <a-select :multiple="multiple"
                                  :default-value="val"
                                  v-model="val"
                                   :disabled="disabled"
                                   :filter-option="filterOption"
                                   allow-search
                                   :options="option"
                                   :virtual-list-props="{height:240}">
                                <template #option="vo">
                                  <span :style="{color:vo.data.color}">{{vo.data.label}}</span>
                                </template>
                                <template #label="vo">
                                  <span :style="{color:vo.data.color}">{{vo.data.label}}</span>
                                </template>
                        </a-select>
                    </template>
                    <template v-else><slot></slot></template>
    </span>`,
    }
});