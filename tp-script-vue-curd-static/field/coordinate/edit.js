define(['/tpscriptvuecurd/field/coordinate/map.js','/tpscriptvuecurd/field/coordinate/BMapGL/map.js','/tpscriptvuecurd/field/coordinate/AMap/map.js'],function(tMap,bMap,aMap){
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
                        <t-map v-if="field.mapType==='TMap'" v-model:value="modelVal" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择位置'" :center="field.center.lng+','+field.center.lat" :z-index="field.zIndex"></t-map>
                        <b-map v-if="field.mapType==='BMapGL'" v-model:value="modelVal" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择位置'" :center="field.center.lng+','+field.center.lat" :z-index="field.zIndex"></b-map>
                        <a-map v-if="field.mapType==='AMap'" v-model:value="modelVal" :disabled="field.readOnly" :placeholder="field.placeholder||'请选择位置'" :center="field.center.lng+','+field.center.lat" :z-index="field.zIndex"></a-map>
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});