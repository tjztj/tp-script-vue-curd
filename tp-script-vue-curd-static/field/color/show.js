define([],function(){
    return {
        props:['info','field'],
        methods:{
            checked(){
                const val=this.info[this.field.name].toString();
                return val===this.field.items[1].value.toString()||val===this.field.items[1].title.toString();
            },
        },
        template: `<div>
                    <div style="display: inline-block;padding: 2px;border:1px solid #dcdfe6;border-radius: 6px;vertical-align: middle">
                        <div :style="{'background-color':info[field.name],height:'calc(1.5715em - 6px)',width:'calc(1.5715em - 6px)','border-radius':'6px'}"></div>
                    </div>
                     <span style="color: #8c8c8c;padding-left: 8px;vertical-align: middle;font-size: 12px">HEX颜色代码：<span v-if="info[field.name]">{{info[field.name]}}</span><span v-else style="color: #bfbfbf">未选择颜色</span></span>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});