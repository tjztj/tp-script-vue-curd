define([],function(){
    return {
        props:['info','field'],
        methods:{
            getVal(){
                const val=this.info[this.field.name].toString();
                return val===this.field.items[1].value.toString()||val===this.field.items[1].title.toString();
            },
        },
        template:`<div>
                    <a-switch :checked="checked" :checked-children="field.items[1].title" :un-checked-children="field.items[0].title" :disabled="true"/>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});