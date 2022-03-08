define(['/tp-script-vue-curd-static.php?field/map_range/map_range.js'],function(mapRange){
    return {
        components:{
            MapRange:mapRange,
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
                       <map-range v-model:value="modelVal" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择区域'" :center="field.center.lng+','+field.center.lat" :district="field.district"></map-range>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});