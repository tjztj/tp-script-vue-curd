define(['/tp-script-vue-curd-static.php?field/coordinate/map.js'],function(tMap){
    return {
        components:{
            tMap,
        },
        props:['field','value','validateStatus'],
        computed:{
            modelVal:{
                get(){
                    return this.value;
                },
                set(val){
                    this.$emit('update:value',val);
                }
            }
        },
        template:`<div class="field-box">
                    <div class="l">
                       <t-map v-model:value="modelVal" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择位置'" :center="field.center.lng+','+field.center.lat"></t-map>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});