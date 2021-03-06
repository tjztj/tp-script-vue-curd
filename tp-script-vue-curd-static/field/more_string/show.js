define([],function(){
    return {
        props:['info','field'],
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <ul class="more-string-box">
                        <li class="more-string-item" v-for="(vo,key) in info[field.name+'Arr']">
                            {{vo}}<span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                        </li>
                    </ul>
                </div>`,
    }
});