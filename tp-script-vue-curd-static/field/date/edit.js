define([], function () {
    const styleId='date-field-edit-dropdown-stype';
    const style = `
<style id="${styleId}">
</style>
`;

    return {
        props: ['field', 'value', 'validateStatus'],
        setup(){
            if (!document.getElementById(styleId)) {
                document.querySelector('head').insertAdjacentHTML('beforeend', style);
            }
          return {
              guid:window.guid(),
          }
        },
        computed: {
            dateDefaultValue: {
                get() {
                    if(this.value===0||this.value==='0'||this.value===null||this.value===undefined){
                        return '';
                    }
                    if (!this.value) {
                        this.$emit('update:value', '');
                        return null;
                    }
                    let val = '';
                    if (/^-?\d+$/g.test(this.value.toString())) {
                        //时间戳
                        val = parseTime(Math.abs(this.value).toString().length<=10?this.value*1000:this.value, this.field.showTime ? '{y}-{m}-{d} {h}:{i}:{s}' : '{y}-{m}-{d}');
                        this.$emit('update:value', val);
                    } else {
                        val = this.value;
                    }
                    return val;
                },
                set(val) {
                    if(val===null||val===undefined){
                        this.$emit('update:value', '');
                        return;
                    }
                    this.$emit('update:value', val);
                },
            },
        },
        methods: {
            disabledDate(val) {
                if (!val) {
                    return false;
                }

                if (!this.field.showTime) {
                    val.setHours(0,0,0,0)
                }

                const values = val.getTime()/1000
                if (this.field.min !== null && values < this.field.min) {
                    return true;
                }
                if (this.field.max !== null && values > this.field.max) {
                    return true;
                }
                return false;
            },
        },
        template: `<div class="field-box">
                    <div class="l">
                        <a-date-picker
                            v-model="dateDefaultValue"
                            type="date"
                            :placeholder="field.placeholder||'请选择日期'"
                            :disabled="field.readOnly"
                            :disabled-date="disabledDate"
                            :show-time="field.showTime"
                            :allow-clear="!field.required"
                            style="width: 100%;"
                        />
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});