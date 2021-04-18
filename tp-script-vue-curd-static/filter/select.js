define([], function () {
    return {
        props: ['config'],
        setup(props,ctx){
            let val='';
            if(props.config.activeValue){
                val=props.config.activeValue;
                if(typeof val==='number'){
                    val=val.toString();
                }
            }
            return {
                inputValue:Vue.ref(val),
                fetching: Vue.ref(false),
                lastFetchId:Vue.ref(0),
                list:Vue.ref([]),
            }
        },
        computed:{
            groupItems(){
                let items={};
                (this.config.url===''?this.config.items:this.list).forEach(v=>{
                    v.group=v.group||'';
                    if(!items[v.group]){
                        items[v.group]=[];
                    }
                    items[v.group].push(v);
                })
                return items;
            },
            haveGroup(){
                for(let i in this.config.items){
                    if(this.config.items[i].group){
                        return true;
                    }
                }
                return false;
            },
            notFoundContent(){
                if(this.config.url===''){
                    return '找不到相关信息'
                }
                if(this.fetching){
                    return undefined;
                }
                return null;
            },
        },
        methods: {
            search(value) {
                if (typeof value === "string") {
                    this.inputValue = value;
                }
                this.$emit('search', this.inputValue);
            },
            filterOption(input, option) {
                return option.props.title.toLowerCase().indexOf(input.toLowerCase()) >= 0;
            },
            getList(value){
                if(this.config.url===''){
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

            }
        },
        template: `<div>
                    <div class="region-value-div">
                        <a-input-group compact size="small">
                             <a-select style="width: 210px" 
                                      v-model:value="inputValue"
                                      allow-clear
                                      show-search 
                                      size="small"
                                      :filter-option="config.url===''?filterOption:false"
                                      :not-found-content="notFoundContent"
                                      @search="getList"
                                      >
                                      
                                      <template v-if="fetching" #not-found-content>
                                          <a-spin size="small" />
                                      </template>
                                      
                                      <template v-if="haveGroup">
                                         <a-select-option value=""><span style="color: rgba(0,0,0,.35);">&nbsp;&nbsp;全部</span></a-select-option>
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
                                         <a-select-option :value="optionItem.value" v-for="optionItem in config.items" :title="optionItem.title"><span :style="{color:optionItem.color}">{{optionItem.title}}</span></a-select-option>
                                     </template>
                            </a-select>
                             <a-button @click="search" size="small">确定</a-button>
                        </a-input-group>
                    </div>
</div>`,
    }
});