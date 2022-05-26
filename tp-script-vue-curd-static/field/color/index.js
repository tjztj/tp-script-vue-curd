define(['qs'], function (Qs) {
    return {
        props: ['record', 'field'],
        template: `<div>
                    <div style="display: inline-block;height: 1em;width: 1em;padding: 2px;border:1px solid #dcdfe6;border-radius: 2px;">
                        <div :style="{'background-color':record.record[field.name],display:'inline-block',height:'100%',width:'100%','border-radius':'2px'}"></div>
                    </div>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});