define([],function(){
    return {
        props:['config'],
        data(){
            return {
                regionValue:'',
            }
        },
        mounted() {
            if(this.config.regionTree.length!==1){
                return;
            }
            if(!this.config.regionTree[0].children||this.config.regionTree[0].children.length!=1){
                this.regionValue=[this.config.regionTree[0].id]
                return;
            }
            if(!this.config.regionTree[0].children[0].children||this.config.regionTree[0].children[0].children.length!=1){
                this.regionValue=[this.config.regionTree[0].id,this.config.regionTree[0].children[0].id]
                return;
            }
            if(!this.config.regionTree[0].children[0].children[0].children||this.config.regionTree[0].children[0].children[0].children.length!=1){
                this.regionValue=[this.config.regionTree[0].id,this.config.regionTree[0].children[0].id,this.config.regionTree[0].children[0].children[0].id]
                return;
            }
        },
        methods: {
            onRegionChange(){
                let val=null;
                if(this.regionValue[2]){
                    val=this.regionValue[2];
                }else if(this.regionValue[1]){
                    val=this.regionValue[1];
                }else if(this.regionValue[0]){
                    val=this.regionValue[0];
                }
                this.$emit('search',val);
            },
        },
        template:`<div>
                  <div class="region-value-div">
                    <a-cascader
                        v-model:value="regionValue"
                        :options="config.regionTree"
                        :placeholder="'请选择'+this.config.title"
                        show-search
                        size="small"
                         change-on-select
                        @change="onRegionChange"
                    />
                 </div>
</div>`,
    }
});