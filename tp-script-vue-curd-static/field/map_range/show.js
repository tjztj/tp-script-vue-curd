define(['/tp-script-vue-curd-static.php?field/map_range/map_range.js'],function(mapRange){
    return {
        components:{
            MapRange:mapRange,
        },
        props:['info','field'],
        template:`<div>
                   <map-range v-model:value="info[field.name]" :disabled="true" :center="field.center.lng+','+field.center.lat" :district="field.district"></map-range>
                </div>`,
    }
});