define([],function(){
    return {
        props:['info','field'],
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <div v-if="info[field.name+'Arr']&&info[field.name+'Arr'].length===1">
                        <template v-for="(vo,key) in info[field.name+'Arr']"> {{vo}}<span class="ext-box" v-if="field.ext">（{{field.ext}}）</span> </template>
                    </div>
                    <ul v-else class="more-string-box">
                        <li class="more-string-item" v-for="(vo,key) in info[field.name+'Arr']">
                            {{vo}}<span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                        </li>
                    </ul>
                </div>`,
    }
});