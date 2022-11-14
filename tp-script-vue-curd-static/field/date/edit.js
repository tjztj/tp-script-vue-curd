define([], function () {
    const styleId='date-field-edit-dropdown-stype';
    const style = `
<style id="${styleId}">
.date-field-edit-dropdown .ant-calendar-time-picker-panel{
    left: 100%;
    padding: 74px 0 33px 0;
    top: -75px;
    background-color: #fff;
    box-shadow: 6px 0px 8px rgb(0 0 0 / 15%);
    width: 160px;
}
.date-field-edit-dropdown .ant-calendar-time-picker-inner{
    border: 1px solid #f0f0f0;
    border-right: 0;
    width: 160px;
}
.date-field-edit-dropdown .ant-calendar-time-picker-panel:before{
    display: block;
    content: ' ';
    position: absolute;
    border-top: 1px solid #f0f0f0;
    left: 0;
    width: 160px;
    top: 34px;
}
.date-field-edit-dropdown .ant-calendar-time-picker-btn{
    display: none!important;
}
.date-field-edit-dropdown .ant-calendar-time-picker+.ant-calendar-body+.ant-calendar-footer-show-ok{
  z-index: 1051;
}
.date-field-edit-dropdown .ant-calendar-time-picker+.ant-calendar-body+.ant-calendar-footer-show-ok .ant-calendar-ok-btn{
margin-right: -160px;
}
</style>
`

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
                    if(this.value===0||this.value==='0'){
                        return '';
                    }
                    if (!this.value) {
                        this.$emit('update:value', '');
                        return null;
                    }
                    let val = '';
                    if (/^\-?\d+$/g.test(this.value.toString())) {
                        //时间戳
                        val = parseTime(Math.abs(this.value).toString().length<=10?this.value*1000:this.value, this.field.showTime ? '{y}-{m}-{d} {h}:{i}:{s}' : '{y}-{m}-{d}');
                        this.$emit('update:value', val);
                    } else {
                        val = this.value;
                    }
                    return moment(val);
                },
                set(val) {
                    if(val===null){
                        this.$emit('update:value', '');
                        return;
                    }
                    this.$emit('update:value', val.format(this.field.showTime ? 'YYYY-MM-DD HH:mm:ss' : 'YYYY-MM-DD'));
                },
            },
        },
        methods: {
            disabledDate(val) {
                if (!val) {
                    return false;
                }
                if (!this.field.showTime) {
                    val.set('hours', 0)
                    val.set('minutes', 0)
                    val.set('seconds', 0)
                }

                const values = val.unix()
                if (this.field.min !== null && values < this.field.min) {
                    return true;
                }
                if (this.field.max !== null && values > this.field.max) {
                    return true;
                }
                return false;
            },
            moment(tm,f){
                return moment(tm,f)
            },
            openChange(status){
                if(!status){
                   return;
                }
                this.$nextTick(()=>{
                    if(!document.querySelector('.dropdown'+this.guid+' .ant-calendar-time-picker')&&document.querySelector('.dropdown'+this.guid+' .ant-calendar-time-picker-btn')){
                        document.querySelector('.dropdown'+this.guid+' .ant-calendar-time-picker-btn').click();
                        this.doOpen();
                    }
                })
            },
            onChange(){
                this.$nextTick(()=>{
                    if(!document.querySelector('.dropdown'+this.guid+' .ant-calendar-time-picker')&&document.querySelector('.dropdown'+this.guid+' .ant-calendar-time-picker-btn')){
                        document.querySelector('.dropdown'+this.guid+' .ant-calendar-time-picker-btn').click();
                        this.doOpen();
                    }
                })
            },
            doOpen(){
                document.querySelectorAll('.dropdown'+this.guid+' .ant-calendar-ym-select a').forEach((btn)=>{
                    if(btn.getAttribute('init')){
                        return;
                    }
                    btn.setAttribute('init','1');
                    btn.addEventListener('click',(e)=>{
                        if(!e.target.classList.contains('ant-calendar-time-status')||e.target.classList.contains('ant-calendar-day-select')){
                            return;
                        }
                        if(!document.querySelector('.dropdown'+this.guid+' .ant-calendar-time-picker')){
                            return;
                        }
                        document.querySelector('.dropdown'+this.guid+' .ant-calendar-time-picker-btn').click();
                        this.$nextTick(()=>{
                            e.target.click();
                        })
                    });
                })



            }
        },
        template: `<div class="field-box">
                    <div class="l">
                        <a-date-picker
                            v-model:value="dateDefaultValue"
                            type="date"
                            :placeholder="field.placeholder||'请选择日期'"
                            :disabled="field.readOnly"
                            :disabled-date="disabledDate"
                            :show-time="field.showTime?{ defaultValue: moment('00:00:00', 'HH:mm:ss') }:false"
                            :allow-clear="!field.required"
                            :dropdown-class-name="'date-field-edit-dropdown dropdown'+guid"
                            @open-change="openChange"
                            @change="onChange"
                            style="width: 100%;"
                        />
                    </div>
                    <div class="r">
                        <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                    </div>
                </div>`,
    }
});