define(['qs'], function (Qs) {
    return {
        props: ['record', 'field'],
        template: `<div>
                    <div style="display: inline-block;padding: 2px;border:1px solid #dcdfe6;border-radius: 2px;vertical-align: middle">
                        <div :style="{'background-color':record.record[field.name],height:'calc(1.5715em - 6px)',width:'calc(1.5715em - 6px)','border-radius':'2px'}"></div>
                    </div>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                </div>`,
    }
});