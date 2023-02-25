define([],function(){
    return {
        props:['info','field'],
        methods:{
            checked(){
                const val=this.info[this.field.name].toString();
                return val===this.field.items[1].value.toString()||val===this.field.items[1].title.toString();
            },
        },
        template:`<div>
                    <a-switch :model-value="checked()" :disabled="true">
                        <template #checked>{{field.items[1].title}}</template>
                        <template #unchecked>{{field.items[0].title}}</template>
                    </a-switch>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});