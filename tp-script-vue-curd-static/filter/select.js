define([], function () {
    const styleId='filter-select-field-style';
    const style = `
<style id="${styleId}">

</style>
`;
    return {
        components: {
            VNodes: (_, { attrs }) => {
                return attrs.vnodes;
            },
        },
        props: ['config'],
        setup(props,ctx){
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }
            const supplyBatch=Vue.ref([]);
            const selectPage=Vue.ref(1);
            const selectSize=Vue.ref(30);
            const selectTotal=Vue.ref(1);
            const selectFilter=Vue.ref('');
            const fetching=Vue.ref(false);

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
                inputValue,
                onParentSearch,
                supplyBatch,
                selectPage,
                selectSize,
                selectTotal,
                selectFilter,
                fetching
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
                return option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0;
            },
            handlePopupScroll(e){
                const { scrollHeight, scrollTop, clientHeight } = e.target
                if (scrollHeight - scrollTop - clientHeight<32) {
                    console.log('触底了')
                    this.selectPage = this.selectPage + 1
                    if(this.selectPage <= this.selectTotal){
                        this.getTaskList()
                    }
                }
            },
            '$post'(...params){
                return window.vueDefMethods.$post.call(this,...params,)
            },
            getTaskList () {
                this.fetching=true;
                this.$post(this.config.url,{
                    page:this.selectPage,
                    keywords:this.selectFilter,
                    pageSize: this.selectSize,
                }).then(res=>{
                    this.selectTotal = Math.ceil(res.data.total/this.selectSize);
                    res.data.data.forEach(v=>{
                        this.supplyBatch.push({
                            label:v[this.config.lableField],
                            value:v[this.config.valueField]
                        })
                    })

                    console.log(res);
                    this.fetching = false;
                }).catch(()=>{
                    this.fetching = false;
                });
            },
            doFilter(value){
                this.selectFilter=value.trim();
                this.selectPage=1;
                this.selectTotal=1;
                this.supplyBatch=[];
                this.getTaskList();
            },
            onFocus(){
                if(this.selectPage!==1||this.selectFilter!==''||this.supplyBatch.length>0){
                    return;
                }
                this.doFilter('');
            }
        },
        template: `<div>
                    <div v-if="config.url!==''" style="padding: 0 0.5em;margin: 0 0.1em;">
                         <a-select style="width: 236px" 
                            v-model:model-value="inputValue"
                            allow-clear
                            allow-search 
                            size="mini"
                            placeholder="输入筛选信息"
                            :multiple="config.multiple"
                            :options="supplyBatch"
                            :loading="fetching"
                            :filter-option="false"
                            @search="doFilter"
                            @change="search"
                            @dropdown-scroll="handlePopupScroll"
                            @focus="onFocus"
                         >
                        </a-select>
                    </div>
                    <div v-else class="region-value-div">
                        <a-select style="width: 236px" 
                              v-model:value="inputValue"
                              allow-clear
                              allow-search 
                              size="mini"
                              placeholder="选择相关信息"
                              :multiple="config.multiple"
                              :filter-option="filterOption"
                              @change="search"
                              >
                              <template v-if="haveGroup">
                                 <a-option value=""><span style="color: rgba(0,0,0,.35);">&nbsp;&nbsp;全部</span></a-option>
                                 <template v-for="(items,key) in groupItems">
                                    <template v-if="key">
                                         <a-optgroup :label="key" :key="key">
                                             <a-option v-for="optionItem in items" :key="optionItem.value" :value="optionItem.value"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-option>
                                         </a-select-opt-group>
                                    </template>
                                     <template v-else>
                                        <a-option v-for="optionItem in items" :key="optionItem.value" :value="optionItem.value"><span :style="{color:optionItem.color}">{{optionItem.text}}</span></a-option>
                                     </template>
                                 </template>
                             </template>
                             <template v-else>
                                 <a-option :value="optionItem.value" :key="optionItem.value" v-for="optionItem in config.items" :title="optionItem.title"><span :style="{color:optionItem.color}">{{optionItem.title}}</span></a-option>
                             </template>
                        </a-select>
                    </div>
</div>`,
    }
});