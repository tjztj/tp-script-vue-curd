define([],function(){
    return {
        props:['info','field'],
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <div class="img-box">
                        <div class="img-box-item" v-for="(vo,key) in info[field.name+'Arr']" @click="showImages(info[field.name+'Arr'],key)">
                            <img :src="vo" />
                        </div>
                    </div>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});