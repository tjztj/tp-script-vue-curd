define([], function () {
    return {
        props: ['config'],
        setup(props,ctx){
            const inputValue=Vue.ref(undefined);
            const onParentSearch = () => {
                let val='';
                if(props.config.activeValue){
                    val=props.config.activeValue;
                    if(typeof val==='number'){
                        val=val.toString();
                    }
                }
                if(val===''){
                    val=undefined;
                }
                inputValue.value=val;
            }
            onParentSearch();


            return {
                inputValue,onParentSearch
            }
        },
        computed:{
            groupItems(){
                let items={};
                this.config.items.forEach(v=>{
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
                                      placeholder="选择相关信息"
                                      :mode="config.multiple?'multiple':null"
                                      :filter-option="filterOption"
                                      @change="search"
                                      >
                                      <template v-if="haveGroup">
                                         <a-select-option value=""><span style="color: rgba(0,0,0,.35);">&nbsp;&nbsp;全部</span></a-select-option>
                                         <template v-for="(items,key) in groupItems">
                                            <template v-if="key">
                                                 <a-select-opt-group :label="key" :key="key">
                                                     <a-select-option v-for="optionItem in items" :key="optionItem.value" :value="optionItem.value"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-select-option>
                                                 </a-select-opt-group>
                                            </template>
                                             <template v-else>
                                                <a-select-option v-for="optionItem in items" :key="optionItem.value" :value="optionItem.value"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-select-option>
                                             </template>
                                         </template>
                                     </template>
                                     <template v-else>
                                         <a-select-option :value="optionItem.value" :key="optionItem.value" v-for="optionItem in config.items" :title="optionItem.title"><span :style="{color:optionItem.color}">{{optionItem.title}}</span></a-select-option>
                                     </template>
                            </a-select>
                             <a-button @click="search" size="small">确定</a-button>
                        </a-input-group>
                    </div>
</div>`,
    }
});