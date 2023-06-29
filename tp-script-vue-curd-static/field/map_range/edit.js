define(['/tpscriptvuecurd/field/map_range/map_range.js','/tpscriptvuecurd/field/map_range/BMapGL/map_range.js','/tpscriptvuecurd/field/map_range/AMap/map_range.js'],function(tMap,bMap,aMap){
    return {
        components:{
            tMap,bMap,aMap
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
                        <t-map v-if="field.mapType==='TMap'" v-model:value="modelVal" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择区域'" :center="field.center.lng+','+field.center.lat" :district="field.district" :z-index="field.zIndex"></t-map>
                        <b-map v-if="field.mapType==='BMapGL'" v-model:value="modelVal" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择区域'" :center="field.center.lng+','+field.center.lat" :district="field.district" :z-index="field.zIndex"></b-map>
                        <a-map v-if="field.mapType==='AMap'" v-model:value="modelVal" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择区域'" :center="field.center.lng+','+field.center.lat" :district="field.district" :z-index="field.zIndex"></a-map>
                    
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});