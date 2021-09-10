define([],function(){
    return {
        props:['info','field'],
        computed:{
            list(){
                if(this.info[this.field.name+'Arr']){
                    return this.info[this.field.name+'Arr'];
                }
                let val=this.info[this.field.name]||'';
                if(!val){
                    return [];
                }
                if(typeof val==='object'){
                    return val;
                }
                return val.split(this.field.separate)
            },
        },
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <div v-if="list.length===1">
                        <template v-for="(vo,key) in list"> {{vo}}<span class="ext-box" v-if="field.ext">（{{field.ext}}）</span> </template>
                    </div>
                    <ul v-else class="more-string-box">
                        <li class="more-string-item" v-for="(vo,key) in list">
                            {{vo}}<span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                        </li>
                    </ul>
                </div>`,
    }
});