define([],function(){
    return {
        props:['config'],
        data(){
            return {
                inputValue:'',
            }
        },
        methods: {
            search(value){
                if(typeof value==="string"){
                    this.inputValue=value;
                }
                this.$emit('search',this.inputValue);
            },
            filterOption(input, option) {
                return option.props.title.toLowerCase().indexOf(input.toLowerCase()) >= 0;
            },
        },
        template:`<div>
                    <div class="region-value-div">
                        <a-input-group compact size="small">
                             <a-select style="width: 210px" 
                                      v-model:value="inputValue"
                                      allow-clear
                                      show-search 
                                      :filter-option="filterOption">
                                      <a-select-option value="">
                                      <span style="color: rgba(0,0,0,.35);">全部</span>
                                    </a-select-option>
                                    <a-select-option :value="optionItem.value" v-for="optionItem in config.items" :title="optionItem.title">
                                        {{optionItem.title}}
                                    </a-select-option>
                            </a-select>
                             <a-button @click="search" size="small">确定</a-button>
                        </a-input-group>
                    </div>
</div>`,
    }
});