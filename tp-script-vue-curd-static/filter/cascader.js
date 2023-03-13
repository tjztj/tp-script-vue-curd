define([],function(){
    return {
        props:['config'],
        setup(props,ctx){
            return {
                val:Vue.ref(props.config.activeValue||'')
            }
        },
        computed:{
            items(){
                if(!this.config.items){
                    return [];
                }
                if(this.config.items.length===1&&this.config.items[0].children){
                    return this.config.items[0].children;
                }
                return this.config.items;
            },
        },
        watch:{
            val(val){
                if(typeof val==='string'||typeof val==='number'){
                    this.$emit('search',val);
                }else if(val&&val.length===0){
                    this.$emit('search','');
                }else{
                    this.$emit('search',val[val.length-1]);
                }
            },
        },
        template:`<div>
                  <div class="region-value-div">
                    <a-cascader
                        v-model:value="val"
                        :options="items"
                        :placeholder="'请选择'+this.config.title"
                        :field-names="{label:'title',}"
                        show-search
                        size="small"
                        change-on-select
                    />
                 </div>
</div>`,
    }
});