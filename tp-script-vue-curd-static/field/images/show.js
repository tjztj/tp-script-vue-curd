define([],function(){
    return {
        props:['info','field'],
        data(){
            return {
                errorUrls:{}
            }
        },
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <div class="img-box">
                        <template v-for="(vo,key) in info[field.name+'Arr']">
                            <div v-show="!(field.removeMissings&&errorUrls[vo])" class="img-box-item" :class="{'curd-img-error-url':errorUrls[vo]}" @click="showImages(info[field.name+'Arr'],key)">
                                <img :src="vo" @error="errorUrls[vo]=true"/>
                            </div>
                        </template>
                        
                    </div>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});