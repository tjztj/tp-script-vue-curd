define([],function(){
    return {
        props:['info','field'],
        methods:{
        },
        template:`<div>
                    <div class="list-field-box">
                        <div class="list-field-item" v-for="(vo,key) in info[field.name+'List']">
                            <div class="list-field-item-row" v-for="v in field.fields">
                                <div class="list-field-item-row-l">{{v.title}}:</div>
                                <div class="list-field-item-row-r"><curd-show-field :info="vo" :field="v"></curd-show-field></div>
                            </div>
                        </div>
                    </div>
                </div>`,
    }
});