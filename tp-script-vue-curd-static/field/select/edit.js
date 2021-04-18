define([],function(){
    return {
        props:['field','value','validateStatus'],
        data:{
            fetching:false,
            lastFetchId:0,
            list:[],
        },
        computed:{
            val:{
                get(){
                    if(this.field.multiple){
                        if(this.value===''){
                            return [];
                        }
                        if(typeof this.value==='number'){
                            return this.value.toString();
                        }
                        if(typeof this.value==='string'){
                            return this.value.split(',');
                        }
                        return [];
                    }
                    return typeof this.value==='undefined'||this.value===''?undefined:this.value.toString();
                },
                set(val){
                    if(val===undefined){
                        this.$emit('update:value', '');
                        return;
                    }
                    this.$emit('update:value', typeof val==='object'?val.join(','):val);
                }
            },
            groupItems(){
                let items={};
                (this.field.url===''?this.field.items:this.list).forEach(v=>{
                    v.group=v.group||'';
                    if(!items[v.group]){
                        items[v.group]=[];
                    }
                    items[v.group].push(v);
                })
                return items;
            },
            haveGroup(){
                for(let i in this.field.items){
                    if(this.field.items[i].group){
                        return true;
                    }
                }
                return false;
            },
            notFoundContent(){
                if(this.field.url===''){
                    return '找不到相关信息'
                }
                if(this.fetching){
                    return undefined;
                }
                return null;
            },
        },
        methods:{
            '$get'(url, params){
                if(url.indexOf('/'+window.VUE_CURD.MODULE+'/')===0){url=url.replace('\/'+window.VUE_CURD.MODULE+'\/','')}
                return service({url, method: 'get',params,headers:{'X-REQUESTED-WITH':'xmlhttprequest'}})
            },
            getList(value){
                if(this.field.url===''){
                    return;
                }
                this.lastFetchId += 1;
                const fetchId = this.lastFetchId;
                this.list=[];
                this.fetching=true;
                this.$get(this.field.url,{search:value}).then(res=>{
                    if (fetchId !== this.lastFetchId) {
                        // for fetch callback order
                        return;
                    }
                    this.list=res;
                    this.fetching=false;
                })
            },
        },
        template:`<div class="field-box">
                    <div class="l">
                        <a-select :mode="field.multiple?'multiple':'default'"
                                  :default-value="val"
                                  v-model:value="val"
                                  :placeholder="field.placeholder||'请选择'+field.title"
                                  :disabled="field.readOnly"
                                  show-search
                                  
                                  :filter-option="field.url===''?filterOption:false"
                                  :not-found-content="notFoundContent"
                                  @search="getList"
                                  >
                                  <template v-if="fetching" #not-found-content>
                                      <a-spin size="small" />
                                  </template>
                                  <template v-if="haveGroup">
                                         <template v-for="(items,key) in groupItems">
                                            <template v-if="key">
                                                 <a-select-opt-group :label="key">
                                                     <a-select-option v-for="optionItem in items" :value="optionItem.value"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-select-option>
                                                 </a-select-opt-group>
                                            </template>
                                             <template v-else>
                                                <a-select-option v-for="optionItem in items" :value="optionItem.value"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-select-option>
                                             </template>
                                         </template>
                                   </template>
                                   <template v-else>
                                        <template v-for="optionItem in field.items">
                                            <a-select-option :value="optionItem.value" v-if="!optionItem.hide"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-select-option>
                                        </template>
                                    </template>
                        </a-select>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});