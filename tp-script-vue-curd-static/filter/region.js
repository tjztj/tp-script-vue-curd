define([],function(){
    return {
        props:['config'],
        setup(props,ctx){
            const regionValue=Vue.ref(props.config.activeValue||'')
            return {
                regionValue:Vue.ref(props.config.activeValue||'')
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
                if(this.regionValue&&this.regionValue[this.regionValue.length-1]){
                    val=this.regionValue[this.regionValue.length-1];
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