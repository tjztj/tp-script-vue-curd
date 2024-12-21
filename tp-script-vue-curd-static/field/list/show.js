define([],function(){
    return {
        props:['info','field'],
        computed:{
            list(){
                const list=this.info[this.field.name+'List'];
                if(typeof list!=='undefined'){
                    return list;
                }
                if(typeof this.info[this.field.name]==='undefined'){
                    return [];
                }

                if(typeof this.info[this.field.name]==='object'){
                    return Object.values(this.info[this.field.name])
                }

                try {
                    return Object.values(JSON.parse(this.info[this.field.name]));
                }catch (e){
                    return [];
                }
            }
        },
        methods:{
        },
        template:`<div>
                    <div class="list-field-box">
                        <div class="list-field-item" v-for="(vo,key) in list">
                            <div class="list-field-item-row" v-for="v in field.fields">
                                <div class="list-field-item-row-l">{{v.title}}:</div>
                                <div class="list-field-item-row-r"><curd-show-field :info="vo" :field="v"></curd-show-field></div>
                            </div>
                        </div>
                    </div>
                </div>`,
    }
});