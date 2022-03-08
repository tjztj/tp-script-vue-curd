define(['/tp-script-vue-curd-static.php?field/coordinate/map.js'],function(tMap){
    return {
        components:{
            tMap,
        },
        props:['info','field'],
        template:`<div>
                   <t-map v-model:value="info[field.name]" :disabled="true" :center="field.center.lng+','+field.center.lat"></t-map>
                </div>`,
    }
});