define(['qs'], function (Qs) {
    return {
        props: ['record', 'field'],
        data() {
            return {
                loading: false,
            }
        },
        computed: {
            checked: {
                get() {
                    return this.getVal();
                },
                set(val) {
                    if (this.field.readOnly || this.field.indexChangeUrl === '') {
                        //不能修改
                        return;
                    }

                    const value=val ? this.field.items[1].value : this.field.items[0].value;
                    this.loading = true;
                    this.$post(this.field.indexChangeUrl, {
                        id: this.record.record.id,
                        [this.field.name]: value
                    }).then(res => {
                        if (res.msg) {
                            antd.message.success(res.msg);
                        }
                        this.record.record[this.field.name]=value;
                    }).finally(() => {
                        this.loading = false
                    })
                }
            }
        },
        methods: {
            getVal() {
                const val = this.record.record[this.field.name].toString();
                return val === this.field.items[1].value.toString() || val === this.field.items[1].title.toString();
            },
            '$post'(url, data) {
                if (window.VUE_CURD.MODULE&&url.indexOf('/' + window.VUE_CURD.MODULE + '/') !== 0&&/^\/?\w+\.php/.test(url)===false&&/^https?:/.test(url)===false) {
                    url = '/' + window.VUE_CURD.MODULE + '/'+url;
                }
                return service({
                    url,
                    method: 'post',
                    data: Qs.stringify(data),
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                        'X-REQUESTED-WITH': 'xmlhttprequest'
                    }
                })
            },
        },
        template: `<div>
                    <a-spin :spinning="loading" size="small">
                     <a-switch v-model:checked="checked" :checked-children="field.items[1].title" :un-checked-children="field.items[0].title" :disabled="field.readOnly||field.indexChangeUrl===''"/>
                    <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                    </a-spin>
                </div>`,
    }
});