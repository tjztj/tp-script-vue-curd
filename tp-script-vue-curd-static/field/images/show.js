define([],function(){
    return {
        props:['info','field'],
        data(){
            return {
                errorUrls:{}
            }
        },
        computed:{
          infos(){
              if(this.info[this.field.name+'Arr']){
                  return this.info[this.field.name+'Arr'];
              }
              if(!this.info[this.field.name]){
                  return [];
              }
              return typeof this.info[this.field.name]==='object'||this.info[this.field.name].split('|');
          },
        },
        methods:{
            showImages(imgs, start){
                window.top.showImages(imgs, start);
            },
        },
        template:`<div>
                    <div class="img-box">
                        <template v-for="(vo,key) in infos">
                            <div class="img-box-item" :class="{'curd-img-error-url':errorUrls[vo]}" @click="showImages(infos,key)">
                                <img :src="vo" @error="errorUrls[vo]=true"/>
                            </div>
                        </template>
                        
                    </div>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});