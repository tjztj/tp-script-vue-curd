define(['/tpscriptvuecurd/field/map_range/map_range.js','/tpscriptvuecurd/field/map_range/BMapGL/map_range.js','/tpscriptvuecurd/field/map_range/AMap/map_range.js'],function(tMap,bMap,aMap){
    return {
        components:{
            tMap,bMap,aMap
        },
        props:['info','field'],
        template:`<div>
                        <t-map v-if="field.mapType==='TMap'" v-model:value="info[field.name]" :disabled="true" :center="field.center.lng+','+field.center.lat" :district="field.district"></t-map>
                        <b-map v-if="field.mapType==='BMapGL'" v-model:value="info[field.name]" :disabled="true" :center="field.center.lng+','+field.center.lat" :district="field.district"></b-map>
                        <a-map v-if="field.mapType==='AMap'" v-model:value="info[field.name]" :disabled="true" :center="field.center.lng+','+field.center.lat" :district="field.district"></a-map>
                    
                </div>`,
    }
});