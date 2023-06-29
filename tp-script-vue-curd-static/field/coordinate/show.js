define(['/tpscriptvuecurd/field/coordinate/map.js','/tpscriptvuecurd/field/coordinate/BMapGL/map.js','/tpscriptvuecurd/field/coordinate/AMap/map.js'],function(tMap,bMap,aMap){
    return {
        components:{
            tMap,bMap,aMap
        },
        props:['info','field'],
        template:`<div>
                   <t-map v-if="field.mapType==='TMap'" v-model:value="info[field.name]" :disabled="true" :center="field.center.lng+','+field.center.lat"></t-map>
                   <b-map v-if="field.mapType==='BMapGL'" v-model:value="info[field.name]" :disabled="true" :center="field.center.lng+','+field.center.lat"></b-map>
                   <a-map v-if="field.mapType==='AMap'" v-model:value="info[field.name]" :disabled="true" :center="field.center.lng+','+field.center.lat"></a-map>
                </div>`,
    }
});