/**
 * tj 1079798840@qq.com
 */
const requires = ['axios', 'qs'];
window.fieldComponents = {};
window.filterComponents = {};
if (vueData.fieldComponents) {
    for (let i in vueData.fieldComponents) {
        let v = vueData.fieldComponents[i];
        if (v.jsUrl) {
            requires.push(v.jsUrl)
            fieldComponents[v.name] = v.jsUrl;
        }
    }
}
if (vueData.filterComponents) {
    for (let i in vueData.filterComponents) {
        requires.push(vueData.filterComponents[i])
        filterComponents[i] = vueData.filterComponents[i];
    }
}

define(requires, function (axios, Qs) {
    /**
     * 自定义Promise
     * @param doFunction
     * @returns {f}
     * @constructor
     */
    function MyPromise(doFunction) {
        const f = function (fn) {
            this.fn = fn;
            return this
        };
        f.prototype._functions = {};
        f.prototype.on = function (event, func) {
            this._functions[event] = func;
            return this
        };
        f.prototype.end = function () {
            this.fn((event, ...params) => {
                if (this._functions[event]) this._functions[event](...params);
                return this
            })
        };
        return new f(doFunction)
    }


    let styles = document.querySelectorAll('#app style');
    if (styles) for (let i = 0; i < styles.length; i++) document.head.appendChild(styles[i]);


    /**
     * 初始化请求
     */
    window.service = axios.create({
        baseURL: '/' + window.VUE_CURD.MODULE + '/',
        withCredentials: true,
        timeout: 150000
    });
    service.interceptors.response.use(async response => {
        const res = response.data;
        if (parseInt(res.code) === 1) {
            return res
        }
        if (res.confirm&&res.confirm.show) {
            return await new Promise((resolve, reject) => {
                antd.Modal.confirm({
                    content: res.msg, okText: res.confirm.okText, cancelText: res.confirm.cancelText,
                    title: Vue.createVNode('b', {}, res.confirm.title),
                    icon: (Vue.openBlock(), Vue.createBlock("svg", {
                        t: "1615779502296",
                        class: "icon anticon",
                        viewBox: "0 0 1024 1024",
                        version: "1.1",
                        xmlns: "http://www.w3.org/2000/svg",
                        width: "22",
                        height: "22"
                    }, [
                        Vue.createVNode("path", {
                            d: "M460.8 666.916571h99.693714v99.620572H460.8V666.916571z m0-398.482285h99.693714v298.861714H460.8V268.434286zM510.756571 19.382857C236.690286 19.382857 12.580571 243.565714 12.580571 517.485714c0 273.993143 221.622857 498.102857 498.102858 498.102857s498.102857-224.109714 498.102857-498.102857c0-273.92-224.182857-498.102857-498.102857-498.102857z m0 896.585143c-219.209143 0-398.482286-179.273143-398.482285-398.482286 0-219.136 179.346286-398.482286 398.482285-398.482285 219.136 0 398.482286 179.346286 398.482286 398.482285 0 219.209143-179.346286 398.482286-398.482286 398.482286z",
                            fill: '#faad14',
                        })
                    ])),
                    onOk() {
                        response.config.headers['confirm-error-code'] = res.errorCode;
                        resolve(service(response.config))
                    }, onCancel() {
                        reject({code: 0, msg: '已取消执行', data: [],})
                    }
                })
            })
        }


        antd.message.error(res.msg || '失败');
        if (res.url && res.url.indexOf(vueData.loginUrl) !== -1) {
            antd.Modal.confirm({
                content: '登录或已过期，可以取消以留在此页，或重新登录', okText: '确认退出', cancelText: '取消', onOk() {
                    location.href = res.url
                }
            })
        }
        return Promise.reject(res)
    }, error => {
        if (typeof error === 'string') {
            error = {code: 0, msg: error, data: [],}
        } else if (!error.msg) {
            console.error(error);
            error = {code: 0, msg: '发生错误', data: [],}
        }
        antd.message.error(error.msg, 6);
        return Promise.reject(error)
    })


    /****窗口方法***/
    window.parseTime = function (t, e) {
        if (0 === arguments.length) return null;
        const n = e || "{y}-{m}-{d} {h}:{i}:{s}";
        let g;
        g = "object" == typeof t ? t : ("number" != typeof (t = "string" == typeof t && /^-?[0-9]+$/.test(t) ? parseInt(t) : t) || 10 !== t.toString().length && 9 !== t.toString().length || (t *= 1e3), new Date(t));
        const r = {
            y: g.getFullYear(),
            m: g.getMonth() + 1,
            d: g.getDate(),
            h: g.getHours(),
            i: g.getMinutes(),
            s: g.getSeconds(),
            a: g.getDay()
        };
        return n.replace(/{(y|m|d|h|i|s|a)+}/g, (t, e) => {
            let n = r[e];
            return "a" === e ? ["日", "一", "二", "三", "四", "五", "六"][n] : (0 < t.length && n < 10 && (n = "0" + n), n || 0)
        })
    };
    window.getMonthWeek = function (dateStr) {
        let date = new Date(dateStr);
        let dateStart = new Date((new Date(dateStr).setDate(1)));
        let firstWeek = 1;
        if (dateStart.getDay() === 1) {
            firstWeek = 1
        } else if (dateStart.getDay() === 0) {
            firstWeek = 8 - 7 + 1
        } else {
            firstWeek = 8 - dateStart.getDay() + 1
        }
        let weekIndex = 1;
        let c = date.getDate();
        if (date.getDay() === 1 && date.getDate() < 7) {
            weekIndex = 1
        } else if (c < firstWeek) {
            weekIndex = -1
        } else {
            if (c < 7) {
                weekIndex = Math.ceil(c / 7)
            } else {
                c = c - firstWeek + 1;
                if (c % 7 === 0) {
                    if (dateStart.getDay() !== 6) {
                        weekIndex = c / 7
                    } else {
                        weekIndex = c / 7 + 1
                    }
                } else {
                    weekIndex = Math.ceil(c / 7)
                }
            }
        }
        let month = date.getMonth();
        let year = date.getFullYear();
        if (weekIndex < 0) {
            if (month == 0) {
                month = 11;
                year--
            } else {
                month--
            }
            let new_date = new Date(year, parseInt(month) + 1, 0);
            return getMonthWeek(new_date.getFullYear() + '-' + (parseInt(new_date.getMonth()) + 1) + '-' + new_date.getDate())
        }
        month++;
        return year + '年' + (month > 9 ? month : ('0' + month)) + '月第' + weekIndex + '周'
    };
    // weekIndexInYear('2020-01-01')//2019年第52周
    window.weekIndexInYear = function (dateStr) {
        let nowDate = new Date(dateStr);
        let initTime = new Date(dateStr);
        initTime.setMonth(0);
        initTime.setDate(1);
        let differenceVal = nowDate - initTime;
        let todayYear = Math.ceil(differenceVal / (24 * 60 * 60 * 1000));
        let index = Math.ceil(todayYear / 7);
        if (index == 0) {
            return weekIndexInYear((nowDate.getFullYear() - 1) + '-12-31')
        }
        return nowDate.getFullYear() + '年第' + index + '周'
    };
    //获取一周的日期范围
    window.getLastWeek = function (dateStr) {
        let date = new Date(dateStr);
        let today = date.getDay();
        let stepSunDay = -today + 1;
        if (today == 0) {
            stepSunDay = -7
        }
        let stepMonday = 7 - today;
        let time = date.getTime();
        let monday = new Date(time + stepSunDay * 24 * 3600 * 1000);
        let sunday = new Date(time + stepMonday * 24 * 3600 * 1000);
        return [monday.getFullYear() + '-' + (monday.getMonth() + 1) + '-' + monday.getDate(), sunday.getFullYear() + '-' + (sunday.getMonth() + 1) + '-' + sunday.getDate(),]
    }
    //生成随机 GUID 数
    window.guid = function () {
        function S4() {
            return (((1 + Math.random()) * 65536) | 0).toString(16).substring(1)
        }

        return (S4() + S4() + "-" + S4() + "-" + S4() + "-" + S4() + "-" + S4() + S4() + S4())
    };

    window.openBox = function (option) {
        window.appParam = this;
        const box = top;
        let vueObj = this;
        if (box.layer) {
            //iframe layui情况下
            return MyPromise(function (trigger) {
                option = Object.assign({
                    title: '',
                    type: 2,
                    area: ['45vw', '100vh'],
                    content: '',
                    maxmin: false,
                    moveOut: false,
                    anim: 2,
                    offset: 'rt',
                    success(layero, index) {
                        let body = box.layui.layer.getChildFrame('body', index);
                        layero.css('overflow', 'hidden');
                        layero.find('iframe')[0].contentWindow.listVue = vueObj;//将当前页面的this保存到新页面的window里面
                        layero.find('iframe')[0].contentWindow.parentWindow = window;
                        layero.close = function () {
                            box.layui.layer.close(index);
                        }
                        if (body.length > 0) {
                            body.attr('layer-index', index);
                            body.on('closeIframe', function () {
                                layero.close();
                            })
                            layero.find('iframe').css('padding', '0px 0 28px 0')
                            box.$.each(body, function (i, v) {
                                box.$(v).before('<style>html, body {background: #ffffff;}body{padding:24px 24px 0 24px;}body #app{padding:0 24px;}</style>');
                            });
                        }
                        trigger('success', layero, index);
                    },
                    end() {
                        trigger('close');
                    }
                }, option);
                if (option.type == 2) {
                    if (option.content.indexOf('is_iframe_goto') == -1) {
                        option.content += (option.content.indexOf('?') == -1 ? '?' : '&') + 'is_iframe_goto=1';
                    }
                    if (option.content.indexOf('is_vue_open') == -1) {
                        option.content += (option.content.indexOf('?') == -1 ? '?' : '&') + 'is_vue_open=1';
                    }
                }
                box.layer.open(option);
            })
        } else {
            if (!box.appParam.bodyModals || !box.appParam.bodyDrawers) {
                box.appParam = box.appPage;
            }
            let appObj = box.appParam;
            //如果不是iframe,打于开当前页面
            return MyPromise(function (trigger) {

                let key;
                const openInfo={
                    visible:false,
                };
                if (option.offset && option.offset === 'auto') {
                    key = 'bodyModals';
                } else {
                    key = 'bodyDrawers';
                    if (option.offset) {
                        switch (option.offset) {
                            case 'l':
                            case 'lt':
                            case 'lb':
                                openInfo.offset = 'left';
                                openInfo.width = openInfo.width||'45vw';
                                break;
                            case 'r':
                            case 'rt':
                            case 'rb':
                                openInfo.offset = 'right';
                                openInfo.width = openInfo.width || '45vw';
                                break;
                            case 't':
                                openInfo.offset = 'top';
                                break;
                            case 'b':
                                openInfo.offset = 'bottom';
                                break;
                            default:
                                openInfo.offset = option.offset;
                        }
                        openInfo.placement = openInfo.offset;
                    } else {
                        option.area = ['45vw', '100vh'];
                    }
                }
                this.openType=key;
                if(appObj[key]){
                    this.openIndex=appObj[key].length;
                }else{
                    this.openIndex=0;
                    appObj[key]=[];
                }


                openInfo.title = option.title || undefined;
                openInfo.url = option.url || option.content || undefined;
                if (option.area) {
                    openInfo.width = option.area[0];
                    openInfo.height = option.area[1];
                }
                openInfo.zIndex = option.zIndex || undefined;
                openInfo.onclose = function () {
                    trigger('close');
                }

                openInfo.onload =  (e)=> {
                    let iframe = e.target;
                    let body = iframe.contentWindow.document.querySelector('body');
                    iframe.contentWindow.listVue = vueObj;//将当前页面的this保存到新页面的window里面
                    iframe.contentWindow.parentWindow = window;
                    let paramData = {
                        iframe,
                        body,
                        option: openInfo,
                        close:()=> {
                            appObj[this.openType][this.openIndex].visible = false;
                            // openInfo.visible = false;
                        }
                    };
                    let myEvent = new Event("closeIframe", {option: openInfo});
                    body.addEventListener("closeIframe", e => {
                        paramData.close()
                    });
                    let style = iframe.contentWindow.document.createElement('style');
                    style.type = 'text/css';
                    style.innerHTML = 'html, body {background: #ffffff;}body{padding:24px 24px 0 24px;}body #app{padding:0 24px;}';
                    iframe.contentWindow.document.querySelector('head').appendChild(style);
                    trigger('success', paramData);
                }
                openInfo.visible = true;
                appObj[key].push(openInfo);
            });
        }
    };

    const uploadOneFile = function (option) {
        if (!option || !option.url) return;

        if (document.querySelector('.upload-one-file-input-box')) {
            document.querySelector('.upload-one-file-input-box').remove();
        }
        if (!option.input) {
            option.input = '<input style="display: none" type="file" name="file" accept="' + (option.accept || '') + '">'
        }
        let boxId = 'upload-one-file-input-box-' + window.guid();
        document.body.insertAdjacentHTML('beforeend', '<div id="' + boxId + '" class="upload-one-file-input-box">' + option.input + '</div>');
        let input = document.querySelector('#' + boxId + ' input');
        let that = this;
        input.onchange = function () {
            let formData = new FormData();
            formData.append("file", this.files[0]);
            that.postDataAndUpload(formData, option.url).then(function (res) {
                if (option.success) option.success(res);
            })
            this.value = '';
        };


        return {
            input,
            trigger() {
                let e = document.createEvent("MouseEvents");
                e.initEvent("click", true, true);
                input.dispatchEvent(e);
            }
        }
    };

    const uploadMethods = {
        postDataAndUpload(formdata, url) {
            return new Promise((resolve, reject) => {
                this.ajaxUpload(formdata, url, function (data) {
                    resolve(data);
                }, function (data) {
                    reject(data);
                })
            })
        },
        ajaxUpload(formdata, url, $function, $error) {
            //ajax伪装
            if (url.indexOf('_ajax=') == -1) {
                if (url.indexOf('?') > -1) {
                    url += "&_ajax=1";
                } else {
                    url += "?_ajax=1";
                }
            }
            let xhr = new XMLHttpRequest();
            xhr = this.doUploadXhr(xhr, $function, $error);
            /* 下面的url一定要改成你要发送文件的服务器url */
            xhr.open("POST", url);
            xhr.send(formdata);
        },
        /**
         * 处理上传监听
         * @param xhr
         * @param $function
         * @param $error
         * @returns {*}
         */
        doUploadXhr(xhr, $function, $error) {
            let is_upload = false, that = this;

            function uploadProgress(evt) {
                if (evt.lengthComputable) {
                    var percentComplete = Math.round(evt.loaded * 100 / evt.total);
                    var current = percentComplete.toString();
                    if (is_upload) {
                        that.showLoadMsg("正在上传文件!（" + current + '%）');
                    } else {
                        if (current < 100) {
                            is_upload = true;
                            that.showLoadMsg('开始上传');
                        }
                    }
                    if (current == 100) {
                        that.showLoadMsg('正在处理...');
                    }
                }
            }

            function uploadComplete(evt) {
                if (evt.target.responseText) {
                    is_upload = false;
                    let data;
                    try {
                        data = JSON.parse(evt.target.responseText);
                    } catch (e) {
                        that.hideLoadMsg();
                        antd.notification.error({message: '服务器发生错误'});
                        if ($error) {
                            $error(e);
                        }
                        return;
                    }

                    if (data.code == 1) {
                        that.hideLoadMsg();
                        // var img_arr = data.data[0] ? data.data : [data.data];//返回一个数组，里面是所有文件上传成功后的信息
                        if ($function) {
                            $function(data);
                        }
                    } else {
                        that.hideLoadMsg();
                        antd.notification.error({message: data.msg,});
                        if ($error) {
                            $error(data);
                        }
                    }
                }
            }

            function uploadFailed(evt) {
                let msg = '上传文件发生了错误尝试!';
                antd.notification.error({message: msg,});
                if ($error) {
                    $error({
                        code: 0,
                        data: [],
                        msg: msg,
                        evt: evt,
                    })
                }
            }

            function uploadCanceled(evt) {
                let msg = '上传被用户取消或者浏览器断开连接!';
                antd.notification.error({message: msg,});
                if ($error) {
                    $error({
                        code: 0,
                        data: [],
                        msg: msg,
                        evt: evt,
                    })
                }
            }

            /* 事件监听 */
            xhr.upload.addEventListener("progress", uploadProgress, false);
            xhr.addEventListener("load", uploadComplete, false);
            xhr.addEventListener("error", uploadFailed, false);
            xhr.addEventListener("abort", uploadCanceled, false);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 2) {
                    // that.showLoadMsg('正在处理...');
                } else if (xhr.readyState == 4) {
                    // console.log(xhr.responseText);
                }
            };
            return xhr;
        },
    }


    const loadMsg = {
        showLoadMsg(text, parentElment) {
            [top.document, document].forEach(v => {
                if (!v.querySelector('#show-load-msg-style')) {
                    v.querySelector('head').insertAdjacentHTML('beforeend', '<style id="show-load-msg-style">.msg-loading-icon{display:block;width:34px;height:34px;margin-left:auto;margin-right:auto;margin-top:-26px;background-image:url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACIAAAAiCAYAAAA6RwvCAAABlElEQVRYR+2WsUoDQRCGvxHFwiZN4gkWNvaCBtRKfAURtBWxsAg5AoqVsRa9O8FSS0ULC19AEVR8AwvBQsjFJL5BvJGVnASJnKJmm7vqYIb5P/6Z3R3hF5/j6QjCBZADGsAL8KARZ1GTy/qaVL9bXr6b2CmvDWTkizrnAjthUa6SdH4FEhcf3NYB6SWLkAXywDwwE8dFCSRip1KSp6+A/gSko1u+5lVZFmGlFX9U2Hguykmn/H8DicWGAh1XZb3lEqKUQld2P8P8O0gs6Ph6+gHTw1xYkLN2mK6BGFHH0zLCpvmPlOmaK7cfc5Q0zX8ddwI9QFlCuG9GzDZcCY1GVx0xghlPM/3ChcAYylbVlbIVECM6GOiCKMdApalMGFe67kjb8BqQhdgVayA5T6d6hBvgrlqUSWsg76fIV3PTDr/2MWoVZMjXI4VFFQpWQRxfV4F9hEO7IJ7OmDVC4douyJ5miaiZXcYqSGtgzWJVtw5i7a3p+mL008cybY21DS2pVWlr0takM5LkQDojSQ6l98hnh94AjEVzlg1mjQ8AAAAASUVORK5CYII=);color:#2196f3;content:"";position:absolute;z-index:100000000;top:50%;bottom:0;left:0;right:0;-webkit-animation:my-loading 0.8s infinite linear;animation:my-loading 0.8s infinite linear;}.loading-msg-div{position:absolute;z-index:9999999999;background-color:hsla(0,0%,100%,.85);margin:0;top:0;right:0;bottom:0;left:0;transition:opacity .3s;}html.loading-msg-parent .loading-msg-div{position:fixed;}.loading-msg-body{width:100%;height:100%;position:relative;}.loading-msg-body.no-text .msg-loading-text{display:none;}.loading-msg-body.no-text .msg-loading-icon{margin-top:0;}.msg-loading-spinner{top:50%;margin-top:-31px;width:100%;text-align:center;position:absolute;}.msg-loading-text{margin:3px 0;padding-top:35px;font-size:14px;color:#5e6d82;line-height:1.5em;}.loading-msg-parent{position:relative !important;min-height:80px;}html.loading-msg-parent,html.loading-msg-parent body{height:auto;}.loading-msg-parent{overflow:hidden;}@keyframes my-loading { 0% { transform: rotate(0deg); } 35% { transform: rotate(179deg); } 100% {  transform: rotate(359deg); } }</style>');
                }
            })
            text = text || '';
            parentElment = parentElment || top.document.querySelector('body');
            if (parentElment.matches('body')) {
                parentElment.closest('html').classList.add('loading-msg-parent')
            } else {
                parentElment.classList.add('loading-msg-parent')
            }
            if (parentElment.querySelector('.loading-msg-div')) {
                parentElment.querySelector('.loading-msg-div .msg-loading-text').innerHTML = text;
            } else {
                let body_class = 'loading-msg-body';
                if (!text) body_class += ' no-text';
                parentElment.insertAdjacentHTML('beforeend', '<div class="loading-msg-div"><div class="' + body_class + '"><div class="msg-loading-spinner"><i class="msg-loading-icon"></i><p class="msg-loading-text">' + text + '</p></div></div></div>');
            }
        },
        hideLoadMsg(parentElment) {
            parentElment = parentElment || top.document.querySelector('body');
            if (parentElment.matches('body')) {
                parentElment.closest('html').classList.remove('loading-msg-parent')
            } else {
                parentElment.classList.remove('loading-msg-parent')
            }
            parentElment.style.minHeight = '';
            if (parentElment.querySelector('.loading-msg-div')) {
                parentElment.querySelector('.loading-msg-div').remove();
            }
        }
    };

    /**
     * 一些自定义的处理函数
     */
    window.vueDefMethods = {
        parseTime: window.parseTime,
        back() {
            window.history.back()
        },
        openBox,
        uploadOneFile,
        ...loadMsg,
        ...uploadMethods,
        '$get'(url, params) {
            if (url.indexOf('/' + window.VUE_CURD.MODULE + '/') === 0) {
                url = url.replace('\/' + window.VUE_CURD.MODULE + '\/', '')
            }
            return service({url, method: 'get', params, headers: {'X-REQUESTED-WITH': 'xmlhttprequest'}})
        },
        '$post'(url, data) {
            if (url.indexOf('/' + window.VUE_CURD.MODULE + '/') === 0) {
                url = url.replace('\/' + window.VUE_CURD.MODULE + '\/', '')
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
        '$request'() {
            return service;
        },
        pageIsInit() {
            if (document.getElementById('app')) document.getElementById('app').style.display = 'block'
            if (document.getElementById('app-loading')) document.getElementById('app-loading').style.display = 'none'
            if (typeof window.top.showImages === 'undefined') {
                window.top.showImages = (imgs, start) => {
                    if (typeof imgs === 'string') {
                        if (imgs === '') {
                            imgs = [];
                        } else {
                            imgs = imgs.split('|');
                        }
                    }
                    if (imgs.length === 0) {
                        return;
                    }

                    this.imgShowConfig.list = imgs;
                    this.$nextTick(() => {
                        let index = 0;
                        if (start) {
                            if (/^\d+$/.test(start.toString())) {
                                index = start;
                            } else {
                                index = imgs.indexOf(index);
                            }
                        }
                        document.querySelectorAll('#vue-curd-imgs-show-box .ant-image-img')[index].click()
                    })
                }
            }
        },
        showImages(imgs, start) {
            window.top.showImages(imgs, start);
        },
        zhCn() {
            const Pagination = {
                items_per_page: '条/页',
                jump_to: '跳至',
                jump_to_confirm: '确定',
                page: '页',
                prev_page: '上一页',
                next_page: '下一页',
                prev_5: '向前 5 页',
                next_5: '向后 5 页',
                prev_3: '向前 3 页',
                next_3: '向后 3 页',
            };
            const DatePicker = {
                lang: {
                    placeholder: '请选择日期',
                    rangePlaceholder: ['开始日期', '结束日期'],
                    today: '今天',
                    now: '此刻',
                    backToToday: '返回今天',
                    ok: '确定',
                    timeSelect: '选择时间',
                    dateSelect: '选择日期',
                    weekSelect: '选择周',
                    clear: '清除',
                    month: '月',
                    year: '年',
                    previousMonth: '上个月 (翻页上键)',
                    nextMonth: '下个月 (翻页下键)',
                    monthSelect: '选择月份',
                    yearSelect: '选择年份',
                    decadeSelect: '选择年代',
                    yearFormat: 'YYYY年',
                    dayFormat: 'D日',
                    dateFormat: 'YYYY年M月D日',
                    dateTimeFormat: 'YYYY年M月D日 HH时mm分ss秒',
                    previousYear: '上一年 (Control键加左方向键)',
                    nextYear: '下一年 (Control键加右方向键)',
                    previousDecade: '上一年代',
                    nextDecade: '下一年代',
                    previousCentury: '上一世纪',
                    nextCentury: '下一世纪',
                }, timePickerLocale: {placeholder: '请选择时间',},
            };
            return {
                locale: 'zh-cn',
                Pagination,
                DatePicker,
                TimePicker: {placeholder: '请选择时间',},
                Calendar: DatePicker,
                ColorPicker: {'btn:save': '保存', 'btn:cancel': '取消', 'btn:clear': '清除',},
                global: {placeholder: '请选择',},
                Table: {
                    filterTitle: '筛选',
                    filterConfirm: '确定',
                    filterReset: '重置',
                    selectAll: '全选当页',
                    selectInvert: '反选当页',
                    sortTitle: '排序',
                    expand: '展开行',
                    collapse: '关闭行',
                },
                Modal: {okText: '确定', cancelText: '取消', justOkText: '知道了',},
                Popconfirm: {cancelText: '取消', okText: '确定',},
                Transfer: {searchPlaceholder: '请输入搜索内容', itemUnit: '项', itemsUnit: '项',},
                Upload: {
                    uploading: '文件上传中',
                    removeFile: '删除文件',
                    uploadError: '上传错误',
                    previewFile: '预览文件',
                    downloadFile: '下载文件',
                },
                Empty: {description: '暂无数据',},
                Icon: {icon: '图标',},
                Text: {edit: '编辑', copy: '复制', copied: '复制成功', expand: '展开',},
                PageHeader: {back: '返回',},
            }
        },
        log(obj) {
            return console.log(obj);
        },
        openOtherBtn(btn,row){
            let w=(btn.modalW||'45vw').toLowerCase();
            let h=(btn.modalH||'100vh').toLowerCase();

            let offset=btn.modalOffset;
            if(!offset){
                offset=h==='100vh'?'rt':'auto';
            }
            if(btn.selfType==='OpenBtn'){
                this.openBox({
                    title:btn.modalTitle,
                    offset:offset,
                    area: [w, h],
                    content: btn.modalUrl,
                }).end();
                return;
            }



            if(!btn.modalFields){
                antd.Modal.confirm({
                    content: btn.modalTitle,
                    title: Vue.createVNode('b', {}, '您确定要执行此操作吗？'),
                    icon: (Vue.openBlock(), Vue.createBlock("svg", {
                        t: "1615779502296",
                        class: "icon anticon",
                        viewBox: "0 0 1024 1024",
                        version: "1.1",
                        xmlns: "http://www.w3.org/2000/svg",
                        width: "22",
                        height: "22"
                    }, [
                        Vue.createVNode("path", {
                            d: "M460.8 666.916571h99.693714v99.620572H460.8V666.916571z m0-398.482285h99.693714v298.861714H460.8V268.434286zM510.756571 19.382857C236.690286 19.382857 12.580571 243.565714 12.580571 517.485714c0 273.993143 221.622857 498.102857 498.102858 498.102857s498.102857-224.109714 498.102857-498.102857c0-273.92-224.182857-498.102857-498.102857-498.102857z m0 896.585143c-219.209143 0-398.482286-179.273143-398.482285-398.482286 0-219.136 179.346286-398.482286 398.482285-398.482285 219.136 0 398.482286 179.346286 398.482286 398.482285 0 219.209143-179.346286 398.482286-398.482286 398.482286z",
                            fill: '#faad14',
                        })
                    ])),
                    onOk:()=>{
                        return new Promise((resolve, reject) => {
                            let option={};
                            if(row){
                                option.id=row.id;
                            }
                            this.$post(btn.saveUrl,option).then(res=>{
                                antd.message.success(res.msg);
                                if(btn.refreshPage){
                                    window.vueDefMethods.showLoadMsg('',document.querySelector('body'))
                                    setTimeout(()=>{
                                        window.location.reload();
                                    },200)
                                    return;
                                }
                                if(btn.refreshList){
                                    this.refreshTable();
                                }else if(row){
                                    this.refreshId( row.id)
                                }
                                resolve()
                            }).catch(err=>{
                                reject(err)
                            })
                        });
                    },
                })
                return;
            }
            this.openBox({
                title:btn.modalTitle,
                area: [w, h],
                offset: offset,
                content:'/tp-script-vue-curd-static.php?row_other_btn/show_inputs.vue',
            }).on('success',function (layero){
                const iframe=layero.iframe?layero.iframe:layero.find('iframe')[0];
                const win=iframe.contentWindow;


                function runScript(script){
                    return new Promise((reslove, rejected) => {
                        // 直接 document.head.appendChild(script) 是不会生效的，需要重新创建一个
                        const newScript = win.document.createElement('script');
                        // 获取 inline script
                        newScript.innerHTML = script.innerHTML;
                        // 存在 src 属性的话
                        const src = script.getAttribute('src');
                        if (src) newScript.setAttribute('src', src);

                        // script 加载完成和错误处理
                        newScript.onload = () => reslove();
                        newScript.onerror = err => rejected();
                        win.document.head.appendChild(newScript);
                        win.document.head.removeChild(newScript);
                        if (!src) {
                            // 如果是 inline script 执行是同步的
                            reslove();
                        }
                    })
                }

                function setHTMLWithScript(container, rawHTML){
                    container.innerHTML = rawHTML;
                    const scripts = container.querySelectorAll('script');

                    return Array.prototype.slice.apply(scripts).reduce((chain, script) => {
                        return chain.then(() => runScript(script));
                    }, Promise.resolve());
                }

                win.VUE_CURD=window.VUE_CURD;
                win.moment=moment;
                win.thatBtn=btn;
                win.beforeInit=function (){
                    win.vueData.title=btn.modalTitle;
                    win.vueData.fields=btn.modalFields;
                    win.vueData.groupFields=btn.modalGroupFields;
                    win.vueData.fieldComponents=btn.modalFieldsComponents;
                    win.vueData.isStepNext=false;
                    win.vueData.stepInfo=null;
                    win.vueData.vueCurdAction='edit';

                    win.vueData.info=btn.info&&Object.keys(btn.info).length>0?btn.info:(row?{id:row.id}:{});
                    win.vueData.subUrl=btn.saveUrl;
                    win.vueData.subBtnTitle=btn.saveBtnTitle;
                }

                let headHtml='';
                let headEls=document.querySelector('head').children;
                for(let i in headEls){
                    if(typeof headEls[i].getAttribute==='function'&&!headEls[i].getAttribute('data-requiremodule')){
                        headHtml+= headEls[i].outerHTML;
                    }
                }

                setHTMLWithScript(win.document.querySelector('head'),"<style id='init-before-style'>body{display: none}</style>"
                    +headHtml
                    +"<script src=\"/tp-script-vue-curd-static.php?require-2.3.6/require.js\" charset=\"utf-8\"></script>"
                    +"<script src=\"/tp-script-vue-curd-static.php?require-config.js\" charset=\"utf-8\"></script>"
                    +"<script>window.beforeInit();setTimeout(()=>{document.querySelector('#init-before-style').remove();require(['/tp-script-vue-curd-static.php?row_other_btn/show_inputs.js']);},100);"
                    +"</script>");


            }).end();

        }
    };


    return function (option) {
        option.data = option.data || function () {
            return {};
        };
        let dt = option.data();
        dt.bodyDrawers=[];
        dt.bodyModals=[];
        dt.imgShowConfig = {
            list: [],
        },
            option.data = () => dt;

        if (!option.mounted) {
            option.mounted = function () {
                this.pageIsInit();
            }
        }
        const beforeMount = option.beforeMount || function () {
        };
        option.beforeMount = function () {
            window.appPage = this;
            beforeMount();
        }

        option.methods = Object.assign(vueDefMethods, option.methods || {});
        window.app = Vue.createApp(option)
        app.use(antd)
        app.component('PlusOutlined', {
            template: `<span role="img" aria-label="plus" class="anticon anticon-plus"><svg class="" data-icon="plus" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><defs><style></style></defs><path d="M482 152h60q8 0 8 8v704q0 8-8 8h-60q-8 0-8-8V160q0-8 8-8z"></path><path d="M176 474h672q8 0 8 8v60q0 8-8 8H176q-8 0-8-8v-60q0-8 8-8z"></path></svg></span>`
        })
        app.component('ReloadOutlined', {
            template: `<span role="img" aria-label="reload" class="anticon anticon-reload"><svg class="" data-icon="reload" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M909.1 209.3l-56.4 44.1C775.8 155.1 656.2 92 521.9 92 290 92 102.3 279.5 102 511.5 101.7 743.7 289.8 932 521.9 932c181.3 0 335.8-115 394.6-276.1 1.5-4.2-.7-8.9-4.9-10.3l-56.7-19.5a8 8 0 00-10.1 4.8c-1.8 5-3.8 10-5.9 14.9-17.3 41-42.1 77.8-73.7 109.4A344.77 344.77 0 01655.9 829c-42.3 17.9-87.4 27-133.8 27-46.5 0-91.5-9.1-133.8-27A341.5 341.5 0 01279 755.2a342.16 342.16 0 01-73.7-109.4c-17.9-42.4-27-87.4-27-133.9s9.1-91.5 27-133.9c17.3-41 42.1-77.8 73.7-109.4 31.6-31.6 68.4-56.4 109.3-73.8 42.3-17.9 87.4-27 133.8-27 46.5 0 91.5 9.1 133.8 27a341.5 341.5 0 01109.3 73.8c9.9 9.9 19.2 20.4 27.8 31.4l-60.2 47a8 8 0 003 14.1l175.6 43c5 1.2 9.9-2.6 9.9-7.7l.8-180.9c-.1-6.6-7.8-10.3-13-6.2z"></path></svg></span>`
        })
        app.component('CheckOutlined', {
            template: `<span role="img" aria-label="check" class="anticon anticon-check"><svg class="" data-icon="check" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg></span>`
        })
        app.component('PictureOutlined', {
            template: `<span role="img" aria-label="picture" class="anticon anticon-picture"><svg class="" data-icon="picture" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M928 160H96c-17.7 0-32 14.3-32 32v640c0 17.7 14.3 32 32 32h832c17.7 0 32-14.3 32-32V192c0-17.7-14.3-32-32-32zm-40 632H136v-39.9l138.5-164.3 150.1 178L658.1 489 888 761.6V792zm0-129.8L664.2 396.8c-3.2-3.8-9-3.8-12.2 0L424.6 666.4l-144-170.7c-3.2-3.8-9-3.8-12.2 0L136 652.7V232h752v430.2zM304 456a88 88 0 100-176 88 88 0 000 176zm0-116c15.5 0 28 12.5 28 28s-12.5 28-28 28-28-12.5-28-28 12.5-28 28-28z"></path></svg></span>`
        })
        app.component('FileImageOutlined', {
            template: `<span role="img" aria-label="file-image" class="anticon anticon-file-image"><svg class="" data-icon="file-image" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M553.1 509.1l-77.8 99.2-41.1-52.4a8 8 0 00-12.6 0l-99.8 127.2a7.98 7.98 0 006.3 12.9H696c6.7 0 10.4-7.7 6.3-12.9l-136.5-174a8.1 8.1 0 00-12.7 0zM360 442a40 40 0 1080 0 40 40 0 10-80 0zm494.6-153.4L639.4 73.4c-6-6-14.1-9.4-22.6-9.4H192c-17.7 0-32 14.3-32 32v832c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V311.3c0-8.5-3.4-16.7-9.4-22.7zM790.2 326H602V137.8L790.2 326zm1.8 562H232V136h302v216a42 42 0 0042 42h216v494z"></path></svg></span>`
        })
        app.component('FileExcelOutlined', {
            template: `<span role="img" aria-label="file-excel" class="anticon anticon-file-excel"><svg class="" data-icon="file-excel" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M854.6 288.6L639.4 73.4c-6-6-14.1-9.4-22.6-9.4H192c-17.7 0-32 14.3-32 32v832c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V311.3c0-8.5-3.4-16.7-9.4-22.7zM790.2 326H602V137.8L790.2 326zm1.8 562H232V136h302v216a42 42 0 0042 42h216v494zM514.1 580.1l-61.8-102.4c-2.2-3.6-6.1-5.8-10.3-5.8h-38.4c-2.3 0-4.5.6-6.4 1.9-5.6 3.5-7.3 10.9-3.7 16.6l82.3 130.4-83.4 132.8a12.04 12.04 0 0010.2 18.4h34.5c4.2 0 8-2.2 10.2-5.7L510 664.8l62.3 101.4c2.2 3.6 6.1 5.7 10.2 5.7H620c2.3 0 4.5-.7 6.5-1.9 5.6-3.6 7.2-11 3.6-16.6l-84-130.4 85.3-132.5a12.04 12.04 0 00-10.1-18.5h-35.7c-4.2 0-8.1 2.2-10.3 5.8l-61.2 102.3z"></path></svg></span>`
        })
        app.component('DownloadOutlined', {
            template: `<span role="img" aria-label="download" class="anticon anticon-download"><svg class="" data-icon="download" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M505.7 661a8 8 0 0012.6 0l112-141.7c4.1-5.2.4-12.9-6.3-12.9h-74.1V168c0-4.4-3.6-8-8-8h-60c-4.4 0-8 3.6-8 8v338.3H400c-6.7 0-10.4 7.7-6.3 12.9l112 141.8zM878 626h-60c-4.4 0-8 3.6-8 8v154H214V634c0-4.4-3.6-8-8-8h-60c-4.4 0-8 3.6-8 8v198c0 17.7 14.3 32 32 32h684c17.7 0 32-14.3 32-32V634c0-4.4-3.6-8-8-8z"></path></svg></span>`
        })
        app.component('DownOutlined', {
            template: `<span role="img" aria-label="down" class="anticon anticon-down"><svg class="" data-icon="down" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M884 256h-75c-5.1 0-9.9 2.5-12.9 6.6L512 654.2 227.9 262.6c-3-4.1-7.8-6.6-12.9-6.6h-75c-6.5 0-10.3 7.4-6.5 12.7l352.6 486.1c12.8 17.6 39 17.6 51.7 0l352.6-486.1c3.9-5.3.1-12.7-6.4-12.7z"></path></svg></span>`
        })
        app.component('SettingOutlined', {
            template: `<span role="img" aria-label="setting" class="anticon anticon-setting"><svg class="" data-icon="setting" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M924.8 625.7l-65.5-56c3.1-19 4.7-38.4 4.7-57.8s-1.6-38.8-4.7-57.8l65.5-56a32.03 32.03 0 009.3-35.2l-.9-2.6a443.74 443.74 0 00-79.7-137.9l-1.8-2.1a32.12 32.12 0 00-35.1-9.5l-81.3 28.9c-30-24.6-63.5-44-99.7-57.6l-15.7-85a32.05 32.05 0 00-25.8-25.7l-2.7-.5c-52.1-9.4-106.9-9.4-159 0l-2.7.5a32.05 32.05 0 00-25.8 25.7l-15.8 85.4a351.86 351.86 0 00-99 57.4l-81.9-29.1a32 32 0 00-35.1 9.5l-1.8 2.1a446.02 446.02 0 00-79.7 137.9l-.9 2.6c-4.5 12.5-.8 26.5 9.3 35.2l66.3 56.6c-3.1 18.8-4.6 38-4.6 57.1 0 19.2 1.5 38.4 4.6 57.1L99 625.5a32.03 32.03 0 00-9.3 35.2l.9 2.6c18.1 50.4 44.9 96.9 79.7 137.9l1.8 2.1a32.12 32.12 0 0035.1 9.5l81.9-29.1c29.8 24.5 63.1 43.9 99 57.4l15.8 85.4a32.05 32.05 0 0025.8 25.7l2.7.5a449.4 449.4 0 00159 0l2.7-.5a32.05 32.05 0 0025.8-25.7l15.7-85a350 350 0 0099.7-57.6l81.3 28.9a32 32 0 0035.1-9.5l1.8-2.1c34.8-41.1 61.6-87.5 79.7-137.9l.9-2.6c4.5-12.3.8-26.3-9.3-35zM788.3 465.9c2.5 15.1 3.8 30.6 3.8 46.1s-1.3 31-3.8 46.1l-6.6 40.1 74.7 63.9a370.03 370.03 0 01-42.6 73.6L721 702.8l-31.4 25.8c-23.9 19.6-50.5 35-79.3 45.8l-38.1 14.3-17.9 97a377.5 377.5 0 01-85 0l-17.9-97.2-37.8-14.5c-28.5-10.8-55-26.2-78.7-45.7l-31.4-25.9-93.4 33.2c-17-22.9-31.2-47.6-42.6-73.6l75.5-64.5-6.5-40c-2.4-14.9-3.7-30.3-3.7-45.5 0-15.3 1.2-30.6 3.7-45.5l6.5-40-75.5-64.5c11.3-26.1 25.6-50.7 42.6-73.6l93.4 33.2 31.4-25.9c23.7-19.5 50.2-34.9 78.7-45.7l37.9-14.3 17.9-97.2c28.1-3.2 56.8-3.2 85 0l17.9 97 38.1 14.3c28.7 10.8 55.4 26.2 79.3 45.8l31.4 25.8 92.8-32.9c17 22.9 31.2 47.6 42.6 73.6L781.8 426l6.5 39.9zM512 326c-97.2 0-176 78.8-176 176s78.8 176 176 176 176-78.8 176-176-78.8-176-176-176zm79.2 255.2A111.6 111.6 0 01512 614c-29.9 0-58-11.7-79.2-32.8A111.6 111.6 0 01400 502c0-29.9 11.7-58 32.8-79.2C454 401.6 482.1 390 512 390c29.9 0 58 11.6 79.2 32.8A111.6 111.6 0 01624 502c0 29.9-11.7 58-32.8 79.2z"></path></svg></span>`
        })
        app.component('SearchOutlined', {
            template: `<span role="img" aria-label="search" class="anticon anticon-search"><svg class="" data-icon="search" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M909.6 854.5L649.9 594.8C690.2 542.7 712 479 712 412c0-80.2-31.3-155.4-87.9-212.1-56.6-56.7-132-87.9-212.1-87.9s-155.5 31.3-212.1 87.9C143.2 256.5 112 331.8 112 412c0 80.1 31.3 155.5 87.9 212.1C256.5 680.8 331.8 712 412 712c67 0 130.6-21.8 182.7-62l259.7 259.6a8.2 8.2 0 0011.6 0l43.6-43.5a8.2 8.2 0 000-11.6zM570.4 570.4C528 612.7 471.8 636 412 636s-116-23.3-158.4-65.6C211.3 528 188 471.8 188 412s23.3-116.1 65.6-158.4C296 211.3 352.2 188 412 188s116.1 23.2 158.4 65.6S636 352.2 636 412s-23.3 116.1-65.6 158.4z"></path></svg></span>`
        })
        app.component('EditOutlined', {
            template: `<span role="img" aria-label="edit" class="anticon anticon-edit"><svg class="" data-icon="edit" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M257.7 752c2 0 4-.2 6-.5L431.9 722c2-.4 3.9-1.3 5.3-2.8l423.9-423.9a9.96 9.96 0 000-14.1L694.9 114.9c-1.9-1.9-4.4-2.9-7.1-2.9s-5.2 1-7.1 2.9L256.8 538.8c-1.5 1.5-2.4 3.3-2.8 5.3l-29.5 168.2a33.5 33.5 0 009.4 29.8c6.6 6.4 14.9 9.9 23.8 9.9zm67.4-174.4L687.8 215l73.3 73.3-362.7 362.6-88.9 15.7 15.6-89zM880 836H144c-17.7 0-32 14.3-32 32v36c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-36c0-17.7-14.3-32-32-32z"></path></svg></span>`
        })
        app.component('CloseOutlined', {
            template: `<span role="img" aria-label="close" class="anticon anticon-close"><svg class="" data-icon="close" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M563.8 512l262.5-312.9c4.4-5.2.7-13.1-6.1-13.1h-79.8c-4.7 0-9.2 2.1-12.3 5.7L511.6 449.8 295.1 191.7c-3-3.6-7.5-5.7-12.3-5.7H203c-6.8 0-10.5 7.9-6.1 13.1L459.4 512 196.9 824.9A7.95 7.95 0 00203 838h79.8c4.7 0 9.2-2.1 12.3-5.7l216.5-258.1 216.5 258.1c3 3.6 7.5 5.7 12.3 5.7h79.8c6.8 0 10.5-7.9 6.1-13.1L563.8 512z"></path></svg></span>`
        })
        app.component('delOutlined', {
            template: `<span role="img" aria-label="close" class="anticon anticon-close"><svg class="" data-icon="close" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M977.455 279.273H47.11c-20.311 0-46.264-26.517-46.264-46.264-0.564-20.875 25.953-46.827 46.264-46.827H233.29V93.09C232.727 32.723 265.451 0 325.818 0h369.543c59.804 0 95.348 32.723 95.348 93.09v93.092H976.89c20.311 0 46.264 26.517 46.264 46.263 0.564 20.31-25.389 46.828-45.7 46.828zM698.182 139.918c0-20.31-26.517-46.263-46.264-46.263H372.646c-20.311 0-46.264 26.517-46.264 46.263v46.264h372.364l-0.564-46.264zM465.737 372.364v465.454h-93.091V372.364h93.09z m186.181 0v465.454h-93.09V372.364h93.09z m-465.454-46.828c20.31 0 46.827 26.517 46.827 46.263v511.718c0 20.311 26.517 46.264 46.264 46.264h465.454c20.311 0 46.264-26.517 46.264-46.264V372.364c0-20.311 26.517-46.264 46.263-46.264 20.31 0 46.263 26.517 46.263 46.264v558.545c0 60.368-32.722 93.091-93.09 93.091H232.163c-60.368 0-93.09-32.723-93.09-93.09V372.363c0.563-20.311 27.08-46.828 47.39-46.828z m0 0"></path></svg></span>`
        })
        app.component('exportOutlined', {
            template: `<span role="img" aria-label="close" class="anticon anticon-close"><svg class="" data-icon="close" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="13493"><path d="M710.208 0.512H210.56v887.04H960V237.184H710.208V0.512z m187.328 531.84v59.328L647.744 768.96V651.008H397.952v-177.28h249.792V355.136l249.792 177.28z" p-id="13494"></path><path d="M772.672 0.512v177.28H960zM152.96 125.056H64V1024h721.92v-87.168H152.96z" p-id="13495"></path></svg></span>`
        })


        /*** 年选择器 ***/
        app.component('AYearPicker', {
            data() {
                return {yearOpen: false,}
            },
            props: ['value'],
            methods: {
                handleOpenChange(status) {
                    this.yearOpen = status
                }, handlePanelChange(value) {
                    this.$emit('update:value', value.format('YYYY'));
                    this.handleOpenChange(false)
                }, clearYear() {
                    this.$emit('update:value', '')
                },
            },
            template: `<a-date-picker v-model:value="value"mode="year"format="YYYY"value-format="YYYY"placeholder="请选择年份"style="width: 100%":open="yearOpen"@open-change="handleOpenChange"@panel-change="handlePanelChange"@change="clearYear"></a-date-picker>`,
        })


        /*** 周选择器 ***/
        app.component('WeekSelect', {
            props: ['value', 'placeholder'],
            setup(props, ctx) {
                let momentVal = null;
                let isOpen = Vue.ref(false);
                let format = () => {
                    if (isOpen.value) {
                        return parseTime(props.value, ' {y}年')
                    }
                    let week = getLastWeek(props.value);
                    return getMonthWeek(props.value) + '（' + week[0] + ' ~ ' + week[1] + '）'
                };
                if (props.value) {
                    if (/^\d+$/g.test(props.value.toString())) {
                        momentVal = parseTime(props.value, '{y}-{m}-{d}');
                        props.value = momentVal
                    } else {
                        momentVal = props.value
                    }
                    momentVal = moment(momentVal);
                    momentVal.format = format
                }
                return {momentVal: Vue.ref(momentVal), isOpen: isOpen, format}
            },
            watch: {
                value(val) {
                    if (!val) {
                        this.momentVal = Vue.ref(null);
                        return
                    }
                    let momentVal = moment(val);
                    momentVal.format = this.format;
                    this.momentVal = Vue.ref(momentVal)
                },
            },
            methods: {
                weekChange(date) {
                    this.$emit('update:value', date.weekday(0).format('YYYY-MM-DD'));
                    date.format = () => {
                        return this.format()
                    }
                }, openWeekChange(status) {
                    this.isOpen = status
                }
            },
            template: `<div><a-week-picker v-model:value="momentVal"type="date":placeholder="placeholder||'请选择周'"style="width: 100%;"@change="weekChange"@open-change="openWeekChange"></a-week-picker></div>`,
        });


        app.component('FieldGroupItem', {
            name: 'fieldGroupItem',
            props: ['groupFieldItems', 'form', 'listFieldLabelCol', 'listFieldWrapperCol', 'fieldHideList', 'info'],
            setup(props, ctx) {
                return {
                    formVal: Vue.ref(props.form),
                    validateStatus: Vue.ref({}),
                }
            },
            computed: {
                currentFieldHideList: {
                    get() {
                        return this.fieldHideList;
                    },
                    set(val) {
                        this.$emit('update:field-hide-list', val)
                    }
                }
            },
            watch: {
                formVal: {
                    handler(formVal) {
                        const checkVal = function (fieldWhere, val) {
                            if (fieldWhere.type === 'in') {
                                for (let i in fieldWhere.valueData) {
                                    if (fieldWhere.valueData[i] == val) {
                                        return true;
                                    }
                                }
                                return false;
                            }
                            if (fieldWhere.type === 'find_in_set') {
                                if (val === '') val = [];
                                const valArr = typeof val === 'object' ? val : val.toString().split(',');
                                for (let i in valArr) {
                                    for(let n in fieldWhere.valueData){
                                        if(fieldWhere.valueData[n]===valArr[i]){
                                            return true;
                                        }
                                    }
                                }
                                return false;
                            }


                            if (fieldWhere.valueData[0] === null) {
                                return val <= fieldWhere.valueData[1];
                            }
                            if (fieldWhere.valueData[1] === null) {
                                return val >= fieldWhere.valueData[0];
                            }
                            return val <= fieldWhere.valueData[1] && val >= fieldWhere.valueData[0];
                        }
                        const checkFieldWhereSelf = (fieldWhere) => {
                            if (fieldWhere.field.name === fieldWhere.RETURN_FALSE_FIELD_NAME) {
                                return fieldWhere.ands&&fieldWhere.ands.length>0;
                            }
                            let val = null;
                            let formValue = formVal[fieldWhere.field.name];
                            if ((fieldWhere.field.type === 'RegionField' || fieldWhere.field.type === 'FilesField')
                                && fieldWhere.field.editShow === false
                                && this.info.sourceData && typeof this.info.sourceData[fieldWhere.field.name] !== 'undefined') {
                                formValue = this.info.sourceData[fieldWhere.field.name];
                            }
                            if (typeof formValue === 'undefined') {
                                if (!this.info || typeof this.info[fieldWhere.field.name] === 'undefined') {
                                    return !fieldWhere.isNot;
                                }
                                val = this.info[fieldWhere.field.name];
                            } else {
                                val = formValue;
                            }
                            if (val === null) {
                                return !fieldWhere.isNot;
                            }

                            if (fieldWhere.field.type === 'DateField' || fieldWhere.field.type === 'MonthField' || fieldWhere.field.type === 'WeekField') {
                                if(!/^\d+$/.test(val.toString())){
                                    val = moment(val).unix()
                                }else if(val < 10000){
                                    val = moment(val+'-01-01').unix()
                                }
                            }

                            if (typeof val === 'object') {
                                if (fieldWhere.field.type === 'RegionField') {
                                    const regionKeys = Object.keys(val);
                                    val = regionKeys.length ? val[regionKeys[regionKeys.length - 1]] : 0;
                                }
                            }
                            return checkVal(fieldWhere, val) !== fieldWhere.isNot;
                        };
                        const checkFieldWhere = function (fieldWhere) {
                            let check = checkFieldWhereSelf(fieldWhere);
                            if (check) {
                                for (let i in fieldWhere.ands) {
                                    if (checkFieldWhere(fieldWhere.ands[i]) === false) {
                                        check = false;
                                        break;
                                    }
                                }
                            }
                            if (check) {
                                return true;
                            }
                            for (let i in fieldWhere.ors) {
                                if (checkFieldWhere(fieldWhere.ors[i])) {
                                    return true;
                                }
                            }
                            return false;
                        };
                        const getWhereFields = function (fieldWhere) {
                            const fields = [];
                            fields.push(fieldWhere.field.name);
                            for (let i in fieldWhere.ands) {
                                fields.push(...getWhereFields(fieldWhere.ands[i]));
                            }
                            for (let i in fieldWhere.ors) {
                                fields.push(...getWhereFields(fieldWhere.ors[i]));
                            }
                            return fields;
                        };
                        ///////////////////////////////////////////////////////////////////////////////////////////////
                        const checkShowItemBy = function (field) {
                            if (!field.items || field.items.length === 0) {
                                return;
                            }
                            //TODO::未完成对TreeSelect的支持
                            field.items.forEach(v => {
                                if (!v.showItemBy) {
                                    delete v.showItem;
                                    return;
                                }
                                if (checkFieldWhere(v.showItemBy)) {
                                    v.showItem = true;
                                } else {
                                    if (formVal[field.name] !== undefined && formVal[field.name] !== '') {
                                        if (field.type === 'CheckboxField' || ((field.type === 'SelectField'||field.type === 'TreeSelect') && field.multiple)) {
                                            let newVals = [];
                                            formVal[field.name].toString().split(',').forEach(val => {
                                                if (val !== v.value.toString()) {
                                                    newVals.push(val);
                                                }
                                            })
                                            const newVal = newVals.join(',');
                                            if (newVal !== formVal[field.name]) {
                                                formVal[field.name] = this.formVal[field.name] = newVal;
                                            }
                                        } else if (formVal[field.name].toString() === v.value.toString()) {
                                            formVal[field.name] = this.formVal[field.name] = '';
                                        }
                                    }

                                    v.showItem = false;
                                }
                            });
                        };
                        ///////////////////////////////////////////////////////////////////////////////////////////////
                        const checkFieldEditTipShow = function (field) {
                            if (field.editTips.length === 0) {
                                field.editTipArr = [];
                                return;
                            }
                            field.editTipArr = field.editTips.filter(val => val.show===null?true:checkFieldWhere(val.show));
                        }

                        ///////////////////////////////////////////////////////////////////////////////////////////////
                        const checkFieldTipShow = function (field) {
                            if (field.tips.length === 0) {
                                field.tipArr = [];
                                return;
                            }
                            field.tipArr = field.tips.filter(val => val.show===null?true:checkFieldWhere(val.show));
                        }

                        ///////////////////////////////////////////////////////////////////////////////////////////////
                        function arrHave(arr, val) {
                            if (typeof arr === 'string') {
                                arr = arr ? arr.split(',') : []
                            }
                            for (let i in arr) {
                                if (arr[i].toString() === val.toString()) {
                                    return true;
                                }
                            }
                            return false;
                        }

                        const changeFieldHideList = (key, fieldName, hide) => {
                            if (hide) {
                                this.currentFieldHideList[key] = this.currentFieldHideList[key] || [];
                                this.currentFieldHideList[key].push(fieldName);
                                return;
                            }
                            if (typeof this.currentFieldHideList[key] === 'undefined') {
                                return;
                            }
                            if (this.currentFieldHideList[key].length > 0) {
                                this.currentFieldHideList[key] = this.currentFieldHideList[key].filter(v => v !== fieldName);
                            }
                            if (this.currentFieldHideList[key].length === 0) {
                                delete this.currentFieldHideList[key]
                            }
                        }
                        const checkHideField = (field, checkVal) => {
                            if (field.hideSelf) {
                                changeFieldHideList(field.name, getWhereFields(field.hideSelf).join(','), checkFieldWhere(field.hideSelf));
                            }

                            let reversalHideFields = !!field.reversalHideFields,
                                oldHideFields = Object.keys(this.currentFieldHideList);
                            if (field.hideFields) {
                                let allFields = [], hideFileds = [], inputVal = '';
                                if (checkVal !== '') {
                                    inputVal = checkVal || '';
                                    //如果是时间格式
                                    if (field.type === 'DateField' || field.type === 'MonthField' || field.type === 'WeekField') {
                                        if(!/^\d+$/.test(checkVal.toString())){
                                            inputVal = moment(checkVal).unix()
                                        }else if(checkVal < 10000){
                                            inputVal = moment(checkVal+'-01-01').unix()
                                        }
                                    } else if (field.type === 'RegionField' && typeof checkVal !== 'number' && typeof checkVal !== 'string') {
                                        const regionKeys = Object.keys(checkVal);
                                        inputVal = regionKeys.length ? checkVal[regionKeys[regionKeys.length - 1]] : 0;
                                    }
                                }

                                let vueIsNull = inputVal === '' || inputVal === 0 || inputVal === '0' || inputVal === null;
                                let isDefHideAboutFields = vueIsNull && field.defHideAboutFields;
                                field.hideFields.filter(item => {
                                    item.fields.forEach(f => {
                                        if (!allFields.includes(f.name)) {
                                            allFields.push(f.name)
                                        }
                                    })
                                    if (vueIsNull) {
                                        return field.defHideAboutFields ? true : false;
                                    }
                                    if (item.start === null && item.end === null) {
                                        return false;
                                    }
                                    if (item.start === null) {
                                        //无限小
                                        return inputVal <= item.end;
                                    }
                                    if (item.end === null) {
                                        //无限大
                                        return inputVal >= item.start;
                                    }
                                    return inputVal >= item.start && inputVal <= item.end;
                                }).forEach(item => {
                                    item.fields.forEach(f => {
                                        if (!hideFileds.includes(f.name)) {
                                            hideFileds.push(f.name)
                                        }
                                    })
                                })
                                allFields.forEach(f => {
                                    changeFieldHideList(f, field.name, isDefHideAboutFields ? true : reversalHideFields !== hideFileds.includes(f));
                                });
                            } else if (field.items && field.items.length > 0) {
                                let hideFileds = [], allFields = [], isDefHideAboutFields = false;
                                field.items.map(item => {
                                    //点击某一个选项时要显示那几个字段,参考桐庐非生产性开支，支出类型
                                    if (item.hideFields && item.hideFields.length > 0) {
                                        item.hideFields.map(hideField => {
                                            if (!allFields.includes(hideField.name)) {
                                                allFields.push(hideField.name)
                                            }
                                            if (checkVal) {
                                                let have;
                                                switch (field.type) {
                                                    case 'CheckboxField':
                                                        have = arrHave(checkVal, item.value);
                                                        break;
                                                    case 'SelectField':
                                                        if (field.multiple) {
                                                            have = arrHave(checkVal, item.value);
                                                        } else {
                                                            have = checkVal.toString() === item.value.toString();
                                                        }
                                                        break;
                                                    default:
                                                        have = checkVal.toString() === item.value.toString();
                                                }
                                                //have 是否符合条件，符合条件就隐藏
                                                // changeFieldHideList(hideField.name,field.name,have)
                                                if (have && !hideFileds.includes(hideField.name)) {
                                                    hideFileds.push(hideField.name)
                                                }
                                            } else if (field.defHideAboutFields) {
                                                // changeFieldHideList(hideField.name,field.name,true)
                                                hideFileds.push(hideField.name);
                                                isDefHideAboutFields = true;
                                            }
                                        })
                                    }
                                })
                                allFields.forEach(f => {
                                    changeFieldHideList(f, field.name, isDefHideAboutFields ? true : reversalHideFields !== hideFileds.includes(f))
                                });
                            }

                            //-----------------
                            //隐藏(显示)其他相关字段
                            let newHideFields = Object.keys(this.currentFieldHideList);
                            oldHideFields.forEach(f => {
                                if (newHideFields.includes(f)) {
                                    return;
                                }
                                //重新显示的字段下面要重新判断
                                let fieldInfos = this.groupFieldItems.filter(v => v.name === f);
                                if (fieldInfos && fieldInfos.length > 0) {
                                    checkHideField(fieldInfos[0], formVal[field.name]);
                                }
                            });
                            newHideFields.forEach(f => {
                                if (oldHideFields.includes(f)) {
                                    return;
                                }
                                //新隐藏的字段
                                let fieldInfos = this.groupFieldItems.filter(v => v.name === f);
                                if (fieldInfos && fieldInfos.length > 0) {
                                    checkHideField(fieldInfos[0], '');
                                }
                            })
                            //-------------------------
                        }
                        ///////////////////////////////////////////////////////////////////////////////////////////////
                        ///////////////////////////////////////////////////////////////////////////////////////////////
                        const setFieldAttrByWhere=(field)=>{
                            const def='--setAttrValByWheres--def--';
                            for(const attr in field.attrWhereValueList){
                                let val=typeof field[attr]==='undefined'?def:field[attr];
                                for(let k=field.attrWhereValueList[attr].length-1;k>=0;k--){
                                    const {value,where}=field.attrWhereValueList[attr][k];
                                    if(where===null||checkFieldWhere(where)){
                                        val=value;
                                        break;
                                    }
                                }
                                if((typeof field[attr]==='undefined'&&val!==def)||field[attr]!==val){
                                    field[attr]=val;
                                }
                            }
                        }


                        this.groupFieldItems.forEach(field => {
                            checkShowItemBy(field);
                            checkFieldTipShow(field);
                            checkFieldEditTipShow(field);
                            checkHideField(field, this.currentFieldHideList[field.name] ? '' : formVal[field.name]);
                            setFieldAttrByWhere(field);
                        });

                        this.$emit('update:form', formVal);
                    },
                    immediate: true,
                    deep: true,
                }
            },
            methods: {
                ...vueDefMethods,
                fieldRules(field) {
                    const required = this.triggerShows(field.name) && field.required;
                    return {
                        required,
                        message: field.title + ' ， 必填',
                    };
                },
                triggerShows(fieldName) {
                    if (!this.currentFieldHideList[fieldName]) {
                        return true;
                    }
                    return false;
                },
                async validateListForm() {
                    //外部调用
                    let isNotErr = true;
                    for (const field of this.groupFieldItems) {
                        if (isNotErr && field.type === 'ListField') {
                            for (let i in this.form[field.name]) {
                                if (this.$refs['listFieldForm' + i]) {
                                    isNotErr = await new Promise(resolve => {
                                        this.$refs['listFieldForm' + i].validate().then(res => {
                                            resolve(true);
                                        }).catch(error => {
                                            if (error.errorFields && error.errorFields[0] && error.errorFields[0].errors && error.errorFields[0].errors[0]) {
                                                antd.message.warning(field.title + ':' + error.errorFields[0].errors[0])
                                            } else {
                                                antd.message.warning('请检测' + field.title + '是否填写正确')
                                            }
                                            console.log('error', error);
                                            resolve(false);
                                        });
                                    })
                                }
                            }

                        }
                    }
                    return isNotErr;
                },
                fieldLabel(field) {
                    if (field.editLabelCol && (field.editLabelCol.span === 0 || field.editLabelCol.span === '0')) {
                        field.editColon = false;
                        return '';
                    }
                    return field.title;
                },
            },
            template: `
                        <div>
                            <div v-for="field in groupFieldItems" :data-name="field.name">
                                <transition name="slide-fade">
                                <a-form-item v-if="field.editShow" v-show="triggerShows(field.name)" :label="fieldLabel(field)" :name="field.name" :rules="fieldRules(field)" :validate-status="validateStatus[field.name]" :label-col="field.editLabelCol" :wrapper-col="field.editWrapperCol" :label-align="field.editLabelAlign" :colon="field.editColon" class="form-item-row">
                                    <div :class="{'field-tips':true,'field-tips-have-items':field.editTipArr&&field.editTipArr.length>0}">
                                        <transition-group name="to-right"><a-alert class="field-tips-item" v-for="item in field.editTipArr" :key="item.guid" :message="item.message" :title="item.title" :banner="!item.border" :closable="item.closable" :icon="item.icon" :show-icon="item.showIcon" :type="item.type"></a-alert></transition-group>
                                    </div>
                                    <div :class="{'field-tips':true,'field-tips-have-items':field.tipArr&&field.tipArr.length>0}">
                                        <transition-group name="to-right"><a-alert class="field-tips-item" v-for="item in field.tipArr" :key="item.guid" :message="item.message" :title="item.title" :banner="!item.border" :closable="item.closable" :icon="item.icon" :show-icon="item.showIcon" :type="item.type"></a-alert></transition-group>
                                    </div>
                                    <component 
                                        :is="'VueCurdEdit'+field.type" 
                                        :field="field" 
                                        v-model:value="formVal[field.name]" 
                                        v-model:validate-status="validateStatus[field.name]" 
                                        v-model:field-hide-list="currentFieldHideList"
                                        :form="formVal"
                                        :info="info"
                                        :list-field-label-col="listFieldLabelCol"
                                        :list-field-wrapper-col="listFieldWrapperCol"
                                        :group-field-items="groupFieldItems"
                                        @submit="$emit('submit',$event)"
                                    ></component>
                                    <transition name="to-right">
                                    <div v-if="field.editExplain" style="color: #bfbfbf">{{field.editExplain}}</div>
                                    </transition>
                                    <transition name="to-right">
                                    <div v-if="field.explain" :style="{color:field.explainColor}">{{field.explain}}</div>
                                    </transition>
                                </a-form-item>
                                </transition>
                            </div>
                        </div>
                    `,
        });

        app.component('CurdShowField', {
            props: ['field', 'info'],
            name: 'CurdShowField',
            data() {
                return {
                    fieldComponents
                }
            },
            computed:{
                tipArr(){
                    if (this.field.tips.length === 0) {
                        return [];
                    }
                    return this.field.tips.filter(val => val.show===null?true:checkFieldWhere(val.show));
                },
            },
            methods: {
                showImages(imgs, start) {
                    if (parseInt(start) != start) {
                        if (start) {
                            let arr = typeof imgs === 'string' ? imgs.split('|') : imgs;
                            let index = arr.indexOf(start);
                            if (index !== -1) {
                                start = index;
                                imgs = arr;
                            }
                        }
                    }
                    window.top.showImages(imgs, start);
                },
            },
            template: `<div class="curd-show-field-box" :data-name="field.name">
                            <div :class="{'field-tips':true,'field-tips-have-items':tipArr.length>0}">
                                <transition-group name="to-right"><a-alert class="field-tips-item" v-for="item in tipArr" :key="item.guid" :message="item.message" :title="item.title" :banner="!item.border" :closable="item.closable" :icon="item.icon" :show-icon="item.showIcon" :type="item.type"></a-alert></transition-group>
                            </div>
                             <component 
                                v-if="fieldComponents['VueCurdShow'+field.type]"
                                :is="'VueCurdShow'+field.type" 
                                :field="field" 
                                :info="info"
                            ></component>
                            <div v-else>
                                <div>{{info[field.name]}}<span class="ext-box" v-if="field.ext">（{{field.ext}}）</span></div>
                            </div>
                            <transition name="to-right">
                             <div v-if="field.explain" :style="{color:field.explainColor}">{{field.explain}}</div>
                             </transition>
                        </div>`,
        });

        /*** 公开表table组件 ***/
        app.component('CurdTable', {
            props: ['childs', 'pagination', 'data', 'loading', 'listColumns','canAdd', 'canEdit', 'actionWidth', 'canDel', 'rowSelection', 'fieldStepConfig', 'actionDefWidth','showCreateTime','setScrollY','childrenColumnName','indentSize','expandAllRows','isTreeIndex'],
            setup(props, ctx) {
                const listColumns = props.listColumns;
                let groupTitles = [], columns = [], titleItems = {}, columnsCount = 0, listFieldComponents = {},
                    fieldObjs = {};
                for (let groupTtitle in listColumns) {
                    groupTitles.push(groupTtitle);
                    let column = {title: groupTtitle, children: []};
                    listColumns[groupTtitle].forEach(function (item) {
                        fieldObjs[item.name] = item;
                        let customTitle = 'custom-title-' + item.name;
                        titleItems[customTitle] = item;
                        let col = {
                            dataIndex: item.name,
                            // title:item.title,
                            slots: {title: customTitle},
                            ellipsis: true,
                            sorter: item.listSort,
                            fixed: item.listFixed ? item.listFixed : false,
                        };
                        if (fieldComponents['VueCurdIndex' + item.type]) {
                            listFieldComponents[item.name] = item;
                            col.slots.customRender = 'field-component-' + item.name;
                        } else {
                            col.slots.customRender = 'default-value';
                        }

                        if (item.listColumnWidth) {
                            col.width = item.listColumnWidth;
                        }
                        columnsCount++;
                        column.children.push(col);
                    })
                    columns.push(column);
                }
                const isGroup = groupTitles.length > 1 || (!listColumns[''] && groupTitles.length > 0);

                if (!isGroup && columns[0]) {
                    columns = columns[0].children;
                }


                const createTimeCol = {
                    // title:'创建时间',
                    ellipsis: true,
                    dataIndex: "create_time",
                    slots: {customRender: 'create-time', title: 'custom-title-create_time'},
                    width: 154,
                    sorter: true,
                };

                if (props.fieldStepConfig && props.fieldStepConfig.enable && props.fieldStepConfig.listShow === true) {
                    const stepCol = {
                        // title:'当前步骤',
                        ellipsis: true,
                        dataIndex: "stepInfo",
                        slots: {customRender: 'step-info', title: 'custom-title-step-info'},
                        fixed: props.fieldStepConfig.listFixed ? props.fieldStepConfig.listFixed : false,
                    };
                    if(props.fieldStepConfig.width&&props.fieldStepConfig.width>0){
                        stepCol.width=props.fieldStepConfig.width;
                    }
                    if (props.fieldStepConfig.listFixed) {
                        stepCol.width = stepCol.width||180;
                        if(props.showCreateTime===undefined||props.showCreateTime){
                            columns.push(createTimeCol)
                            columnsCount++;
                        }
                        columns.push(stepCol)
                        columnsCount++;
                    } else {
                        columns.push(stepCol)
                        columnsCount++;
                        columns.push(createTimeCol)
                        columnsCount++;
                    }
                } else {
                    if(props.showCreateTime===undefined||props.showCreateTime){
                        columns.push(createTimeCol)
                        columnsCount++;
                    }
                }


                //可prop动态设置宽度
                const newActionW = Vue.ref(props.actionDefWidth || (32+28));
                columns.push({
                    // title:'操作',
                    slots: {customRender: 'action', title: 'custom-title-action'},
                    width: newActionW,
                    fixed: 'right',
                })
                columnsCount++;
                let id = 'pub-default-table-' + window.guid();

                //太小出现滚动条
                let scrollX = Vue.ref(undefined);
                let scrollY = Vue.ref(undefined);
                let getX = function () {
                    if (document.body.clientWidth > 1640) {
                        return undefined;
                    }
                    if (columnsCount <= 3) {
                        return document.body.clientWidth > 370 ? undefined : 420;
                    }
                    if (columnsCount <= 4) {
                        return document.body.clientWidth > 450 ? undefined : 500;
                    }
                    if (columnsCount <= 5) {
                        return document.body.clientWidth > 680 ? undefined : 960;
                    }
                    if (columnsCount < 7) {
                        return document.body.clientWidth > 790 ? undefined : 1080;
                    }
                    if (columnsCount < 9) {
                        return document.body.clientWidth > 910 ? undefined : 1240;
                    }
                    if (columnsCount < 12) {
                        return document.body.clientWidth > 1460 ? undefined : 1560;
                    }
                    return 1640;
                }
                let getY = async function () {
                    let h = undefined;
                    await Vue.nextTick(async function () {
                        await new Promise(async function (resolve) {
                            setTimeout(function (){
                                let parent = document.getElementById(id).parentNode;
                                let elH = (parent.querySelector('.ant-table-body .ant-table-fixed') || parent.querySelector('.ant-table-body .ant-table-tbody')).clientHeight;
                                let theadH = (parent.querySelector('.ant-table-header .ant-table-fixed') || parent.querySelector('.ant-table-body .ant-table-thead')|| parent.querySelector('.ant-table-header .ant-table-thead')).clientHeight;
                                let pageH=parent.querySelector('ul.ant-table-pagination')?parent.querySelector('ul.ant-table-pagination').clientHeight:0;
                                if(pageH){
                                    pageH+=32;
                                }else{
                                    pageH=0;
                                }
                                if (document.body.clientHeight >= elH && (parent.clientHeight - theadH - pageH) >= elH) {
                                    resolve()
                                    return;
                                }
                                h = parent.clientHeight - theadH - pageH;
                                resolve()
                            },40)
                        })
                    })
                    return h;
                };

                const columnsVals=Vue.ref(columns);
                let onresize = () => {
                    scrollX.value = getX();
                    if(scrollX.value===undefined){
                        const tablePath='#' + id+'>.curd-table .ant-table-default>.ant-table-content>.ant-table-body>table';
                        if(!document.querySelector('#' + id)
                        ||!document.querySelector('#' + id),document.querySelector(tablePath)){
                            if(!document.querySelector('#' + id+'>.curd-table table')||!document.querySelector('#' + id+'>.curd-table .ant-table-body')){
                                setTimeout(()=>{
                                    onresize();
                                },40)
                            }
                        }else{
                            scrollX.value=document.querySelector('#' + id).clientWidth;
                        }
                    }


                    columnsVals.value.forEach(col=>{
                        if(typeof col.fixed!=='undefined'){
                            if(scrollX.value===undefined){
                                if(typeof col.fixedOld==='undefined'){
                                    col.fixedOld=col.fixed;
                                }
                                col.fixed=false;
                            }else if(typeof col.fixedOld!=='undefined'){
                                col.fixed=col.fixedOld;
                            }
                        }
                    })

                    if(props.setScrollY){
                        getY().then(res => {
                            scrollY.value = res;
                        })
                    }
                };
                Vue.nextTick(function (){
                    onresize();
                    const oldResize=window.onresize||function (){};
                    window.onresize = (e)=>{
                        oldResize(e);
                        onresize();
                    };
                })



                let childsObjs = {};
                if (props.childs) {
                    props.childs.forEach(v => {
                        childsObjs[v.name] = v;
                    })
                }

                return {
                    actionW: newActionW,
                    columns: columnsVals,
                    isGroup,
                    titleItems,
                    scrollX,
                    scrollY,
                    id,
                    listFieldComponents,
                    fieldObjs,
                    childsObjs,
                    onresize,
                    expandedRowKeys:Vue.ref([]),
                }
            },
            watch: {
                actionWidth(val) {
                    this.getActionWidthByProps()
                },
                data(data) {
                    Vue.nextTick( ()=>{
                        this.onresize();
                        setTimeout(()=>{
                            this.onresize();
                        },40)
                    });
                    this.getActionWidthByProps();
                    const expandedRowKeys=[];
                    if(this.expandAllRows){
                        const setPids= list=>{
                            list.forEach(v=>{
                                if(v[this.childrenColumnName]&&v[this.childrenColumnName].length>0){
                                    expandedRowKeys.push(v.id);
                                    setPids(v[this.childrenColumnName])
                                }
                            })
                        }
                        setPids(data);
                    }
                    this.expandedRowKeys=expandedRowKeys;
                }
            },
            methods: {
                getActionWidthByProps() {
                    let btnWidth = 28, defW = 0;
                    this.data.forEach(record => {
                        let stepWidth = 0;
                        if (this.stepBtnShow(record) && record.nextStepInfo.config.listBtnText) {
                            if (record.nextStepInfo.config.listBtnWidth) {
                                stepWidth = record.nextStepInfo.config.listBtnWidth;
                            } else if (record.nextStepInfo.config.listBtnText) {
                                stepWidth = this.getTextWidthByBtn(record.nextStepInfo.config.listBtnText);
                            }
                        }


                        let childW = 0;
                        if (record.childBtns) {
                            for (let childName in record.childBtns) {
                                if (record.childBtns[childName].show) {
                                    childW += this.getTextWidthByBtn(record.childBtns[childName].text);
                                }
                            }
                        }

                        let childAddW=0;
                        if(this.isCanAddChildren(record)){
                            childAddW = this.getTextWidthByBtn(this.addChildrenBtnText(record))
                        }

                        let showW = 0;
                        if (this.isCanShowInfo(record)) {
                            showW = this.getTextWidthByBtn(this.showBtnText(record))
                        }


                        let editW = 0;
                        if (this.isCanEdit(record)) {
                            editW = this.getTextWidthByBtn(this.editBtnText(record))
                        }

                        let delW = 0;
                        if (this.isCanDel(record)) {
                            delW = 31;
                        }
                        const btnW = stepWidth + childW + childAddW + showW + editW + delW +this.getBeforeBtnsW(record) + this.getAfterBtnsW(record) - 14;//要删掉一个间隔
                        if (btnW > btnWidth) {
                            btnWidth = btnW;
                        }

                        if (typeof this.actionWidth === 'function') {
                            const newdefW = this.actionWidth(record);
                            if (newdefW > defW) {
                                defW = newdefW;
                            }
                        } else if (this.actionWidth) {
                            defW = this.actionWidth;
                        }
                    })


                    this.actionW = 32 + btnWidth + defW;
                },
                handleTableChange(pagination, filters, sorter) {
                    this.$emit('change', pagination, filters, sorter, this.data)
                },
                openAddChildren(row) {
                    this.$emit('openAddChildren', row)
                },
                openEdit(row) {
                    this.$emit('openEdit', row)
                },
                openShow(row) {
                    this.$emit('openShow', row)
                },
                openNext(row) {
                    this.$emit('openNext', row)
                },
                openChildList(row, modelInfo, btn) {
                    this.$emit('openChildList', row, modelInfo, btn)
                },
                onDelete(row) {
                    this.$emit('onDelete', row)
                },
                stepBtnShow(record) {
                    return this.fieldStepConfig && this.fieldStepConfig.enable && record.stepNextCanEdit && record.nextStepInfo;
                },
                getTextWidthByBtn(text) {
                    text = text || '';
                    return 17 + (text.split('').length * 14);
                },
                addChildrenBtnText(row){
                    return row.childAddBtn&&row.childAddBtn.btnTitle?row.childAddBtn.btnTitle:'添加下级';
                },
                addChildrenBtnColor(row) {
                    if(!row.childAddBtn){
                        return null;
                    }
                    if(!row.childAddBtn.btnColor){
                        return null;
                    }
                    return row.childAddBtn.btnColor;
                },
                showBtnText(row) {
                    return row.showBtn&&row.showBtn.btnTitle?row.showBtn.btnTitle:'详情';
                },
                showBtnColor(row) {
                    if(!row.showBtn){
                        return null;
                    }
                    if(!row.showBtn.btnColor){
                        return null;
                    }
                    return row.showBtn.btnColor;
                },
                editBtnText(row) {
                    let editText = row.editBtn&&row.editBtn.btnTitle?row.editBtn.btnTitle:'修改';
                    if (this.fieldStepConfig && this.fieldStepConfig.enable && row.stepInfo && row.stepInfo.config.listBtnText) {
                        if (row.stepInfo.config.listBtnTextEdit !== '') {
                            return row.stepInfo.config.listBtnTextEdit;
                        }
                        editText += row.stepInfo.config.listBtnText;
                    }
                    return editText;
                },
                editBtnColor(row) {
                    if (this.fieldStepConfig && this.fieldStepConfig.enable && row.stepInfo && row.stepInfo.config.listBtnText) {
                        return row.stepInfo.config.listBtnColorEdit;
                    }
                    if(!row.editBtn){
                        return null;
                    }
                    if(!row.editBtn.btnColor){
                        return null;
                    }
                    return row.editBtn.btnColor;
                },
                isCanShowInfo(row) {
                    return typeof row.__auth === 'undefined' || typeof row.__auth.show === 'undefined' || row.__auth.show === true;
                },
                isCanEdit(row) {
                    return this.canEdit !== false && (!row.__auth || typeof row.__auth.edit === 'undefined' || row.__auth.edit === true) && (!this.fieldStepConfig || !this.fieldStepConfig.enable || (row.stepFields && row.stepFields.length > 0 && row.stepCanEdit))
                },
                isCanDel(row) {
                    return this.canDel && (!row.__auth || typeof row.__auth.del === 'undefined' || row.__auth.del === true)
                },
                isCanAddChildren(row){
                    return this.isTreeIndex&&this.canAdd;
                },
                onExpand (expanded, record) {
                    if (expanded) {
                        // 设置展开窗Key，代表展开操作
                        this.expandedRowKeys.push(record.id)
                    } else {
                        // 代表折叠操作
                        if (this.expandedRowKeys.length) {
                            this.expandedRowKeys = this.expandedRowKeys.filter(v => {
                                return v !== record.id
                            })
                        }
                    }
                },
                getBeforeBtns(row){
                    return row.otherBtns?row.otherBtns.before:[];
                },
                getAfterBtns(row){
                    return row.otherBtns?row.otherBtns.after:[];
                },
                getBeforeBtnsW(row){
                    let w=0;
                    const btns=this.getBeforeBtns(row);
                    for(let i in btns){
                        if(btns[i].btnTitle)w+=this.getTextWidthByBtn(btns[i].btnTitle)
                    }
                    return w;
                },
                getAfterBtnsW(row){
                    let w=0;
                    const btns=this.getAfterBtns(row);
                    for(let i in btns){
                        if(btns[i].btnTitle)w+=this.getTextWidthByBtn(btns[i].btnTitle)
                    }
                    return w;
                },
                refreshId(id){
                    this.$emit('refreshId', id)
                },
                refreshTable(){
                    this.$emit('refreshTable')
                },
                '$post':vueDefMethods.$post,
                openBox:window.openBox,
                openOtherBtn:window.vueDefMethods.openOtherBtn,
            },
            template: `<div :id="id">
                        <a-table
                            :row-key="record => record.id"
                            :columns="columns"
                            :data-source="data"
                            :pagination="pagination&&(!isTreeIndex)&&pagination.pageSize?pagination:false"
                            :loading="loading"
                            @change="handleTableChange"
                            class="curd-table"
                            :bordered="isGroup"
                            :scroll="{ x: scrollX ,y:scrollY}"
                            :row-selection="rowSelection"
                            :children-column-name="childrenColumnName"
                            :indent-size="indentSize"
                            v-model:expanded-row-keys="expandedRowKeys"
                            @expand="onExpand"
                        >
                            <template #[key] v-for="(item,key) in titleItems">
                                <slot :name="key" :field="item" :columns="columns">
                                    <div style="white-space:normal;line-height: 1.14">
                                        <span>{{item.title}}</span>
                                        <span v-if="item.ext" style="color: #bfbfbf">（{{item.ext}}）</span>
                                    </div>
                                </slot>
                                
                            </template>
                            
                             <template #['field-component-'+item.name]="record" v-for="item in listFieldComponents">
                                 <slot :name="'f-'+item.name"
                                    :field="item" 
                                    :record="record">
                                   <component 
                                        :is="'VueCurdIndex'+item.type" 
                                        :field="item" 
                                        :record="record"
                                        @refresh-table="$emit('refreshTable')"
                                    ></component>
                                </slot>
                             </template>
               
                             
                             
                             <template #default-value="record">
                                <slot :name="'f-'+record.column.dataIndex"
                                    :field="fieldObjs[record.column.dataIndex]" 
                                    :record="record">
                                    <a-tooltip placement="topLeft">
                                        <template #title>{{record.text}}</template>
                                        {{record.text}}
                                    </a-tooltip>
                                </slot>
                             </template>
                             
                             <template #custom-title-step-info><slot name="custom-title-step-info" :columns="columns">当前步骤</slot></template>
                             <template #step-info="{ text: stepInfo }">
                                    <slot name="step-info">
                                        <div class="curd-table-row-step-div">
                                            <div class="curd-table-row-step-title">
                                                <a-tooltip v-if="stepInfo" placement="leftTop">
                                                    <template #title>{{ stepInfo.title }}</template>
                                                    <span :style="{color:stepInfo.config.color||'inherit'}">{{ stepInfo.title }}</span>
                                                </a-tooltip>
                                            </div>
                                            <div class="curd-table-row-step-other">
                                                <template v-for="item in stepInfo.tags"><a-tag v-if="item.text" :color="item.color">{{item.text}}</a-tag></template>
                                                <a-popover v-if="stepInfo.remark" trigger="click">
                                                      <template #content>{{stepInfo.remark}}</template>
                                                      <a class="curd-table-row-step-other-more">{{stepInfo.remarkBtnText}}</a>
                                                </a-popover>
                                            </div>
                                        </div>
                                    </slot>
                             </template>
                             
                             
                             
                             <template #custom-title-create_time><slot name="custom-title-create_time" :columns="columns">创建时间</slot></template>
                             <template #create-time="{ text: create_time }">
                                    <slot name="f-create_time">
                                        <a-tooltip>
                                            <template #title>{{ create_time }}</template>
                                            {{ create_time }}
                                        </a-tooltip>
                                    </slot>
                             </template>
                             
                             
                              
                             <template #custom-title-action><slot name="custom-title-action" :columns="columns">操作</slot></template>
                             <template #action="{ record }">
                                    <slot name="do-before" :record="record">
                                   
                                    </slot>
                                    
                                    <template v-for="btn in getBeforeBtns(record)">
                                        <a @click="openOtherBtn(btn,record)" :style="{color: btn.btnColor}">{{btn.btnTitle}}</a>
                                        <a-divider type="vertical"></a-divider>
                                    </template>
                                    
                                    <slot name="do" :record="record">
                                        <a v-if="isCanShowInfo(record)" @click="openShow(record)" :style="{color: showBtnColor(record)}">{{showBtnText(record)}}</a>
                                          
                                        <template v-if="isCanEdit(record)">
                                            <a-divider v-if="isCanShowInfo(record)" type="vertical"></a-divider>
                                            <a @click="openEdit(record)" :style="{color: editBtnColor(record)}">{{editBtnText(record)}}</a>
                                        </template>
                                    </slot>
                                    
                                    <template v-if="stepBtnShow(record)">
                                         <slot name="step-next-btn" :record="record">
                                            <template v-if="record.nextStepInfo.config.listBtnText">
                                                <a-divider type="vertical"></a-divider>
                                                <a @click="openNext(record)" :style="{color:record.nextStepInfo.config.listBtnColor}" :class="record.nextStepInfo.config.listBtnClass" class="open-step-a-class">{{record.nextStepInfo.config.listBtnText}}</a>
                                            </template>
                                         </slot>
                                    </template>
                                    
                                    <slot name="child-btns" :record="record">
                                        <template v-if="record.childBtns">
                                        <template v-for="(vo,kk) in record.childBtns">
                                        <template v-if="vo.show">
                                            <a-divider type="vertical"></a-divider>
                                            <a @click="openChildList(record,childsObjs[kk],vo)" :style="{color: vo.color}" class="open-child-a-class">{{vo.text}}</a>
                                        </template>
                                        </template>
                                        </template>
                                    </slot>
                                    
                                    
                                    <template v-if="isCanAddChildren(record)">
                                        <a-divider type="vertical"></a-divider>
                                        <a @click="openAddChildren(record)" :style="{color: addChildrenBtnColor(record)}">{{addChildrenBtnText(record)}}</a>
                                    </template>
                                    
                                      <template v-for="btn in getAfterBtns(record)">
                                        <a-divider type="vertical"></a-divider>
                                         <a @click="openOtherBtn(btn,record)" :style="{color: btn.btnColor}">{{btn.btnTitle}}</a>
                                      </template>
                                    
                                    <slot name="do-after" :record="record">
                                     
                                    </slot>
                                    
                                    <template v-if="isCanDel(record)">
                                            <a-divider type="vertical"></a-divider>
                                            <a-popconfirm
                                                placement="left"
                                                title="您确定要删除这条数据吗？"
                                                @confirm="onDelete(record)"
                                              >
                                              <del-outlined class="pub-remove-icon"></del-outlined>
                                            </a-popconfirm>
                                    </template>
                            </template>
                                
                            <template v-if="$slots.footer" #footer><slot name="footer" :columns="columns" :current-page-data="data"></slot></template>
                        </a-table>
                    </div>`,
        })


        /*** 筛选组件 ***/
        app.component('CurdFilter', {
            props: ['filterConfig', 'name', 'class', 'title', 'childs', 'filterValues', 'loading'],
            setup(props, ctx) {
                let filterConfig = props.filterConfig.map(function (v) {
                    if (v.group) {
                        v.title = v.group + ' >' + v.title
                    }
                    return v;
                })
                let filterSource = Vue.ref({filterConfig});
                let modelTitles = {
                    [props.class]: props.title,
                    [props.name]: props.title,
                };
                for (let i in props.childs) {
                    filterSource.value[props.childs[i].name] = props.childs[i].filterConfig.map(function (v) {
                        if (v.group) {
                            v.title = v.group + ' >' + v.title
                        }
                        return v;
                    })

                    modelTitles[props.childs[i].class] = props.childs[i].title;
                    modelTitles[props.childs[i].name] = props.childs[i].title;
                }

                return {
                    filterSource,
                    modelTitles,
                    filterData: Vue.ref({}),
                    childFilterData: Vue.ref({}),
                    showMoreFilter: Vue.ref(false),
                }
            },
            computed: {
                base() {
                    return {
                        name: 'filterConfig',
                        filterConfig: this.filterSource.filterConfig,
                        filterData: this.filterData
                    }
                }
            },
            methods: {
                filterGroupIsShow(child) {
                    for (let i in this.filterSource[child.name]) {
                        if (this.filterGroupItemIsShow(this.filterSource[child.name][i], child)) {
                            return true;
                        }
                    }
                    return false
                },
                filterGroupItemIsShow(item, child) {
                    return item.show && (!child.filterData || !child.filterData[item.name]);
                },
                search(val, item) {
                    item.activeValue = val;
                    const data=this.getFilterData();
                    if(this.$refs){
                        for(let key in this.$refs){
                            if(key.indexOf('filters.')===0&&typeof this.$refs[key].onParentSearch==='function'){
                                this.$refs[key].onParentSearch();
                            }
                        }
                    }
                    this.$emit('search',data);
                },
                getFilterData() {
                    let curdFilters = [], curdChildFilters = {}, haveHide = false;
                    if (this.filterSource.filterConfig && this.filterSource.filterConfig.length > 0) {
                        curdFilters = this.filterSource.filterConfig.filter(v => v.show);
                        haveHide = curdFilters.length !== this.filterSource.filterConfig.length;
                    }
                    for (let key in this.filterSource) {
                        if (key !== 'filterConfig') {
                            if (this.filterSource[key].length > 0) {
                                curdChildFilters[key] = this.filterSource[key].filter(v => v.show);
                                haveHide = haveHide || curdChildFilters[key].length !== this.filterSource[key].length;
                            }
                        }
                    }

                    let filterData = {};
                    curdFilters.forEach(function (v) {
                        if (typeof v.activeValue !== 'undefined' && v.activeValue !== null) {
                            filterData[v.name] = v.activeValue;
                        }
                    })
                    if (this.filterValues) {
                        filterData = Object.assign(filterData, this.filterValues);
                    }


                    let childFilterData = {};
                    for (let key in curdChildFilters) {
                        curdChildFilters[key].forEach(function (v) {
                            if (typeof v.activeValue !== 'undefined' && v.activeValue !== null) {
                                childFilterData[key] = childFilterData[key] || {};
                                childFilterData[key][v.name] = v.activeValue;
                            }
                        })
                    }
                    if (this.childs) {
                        let allFilterChildValues = {};
                        this.childs.forEach(v => {
                            if (v.filterData) {
                                //如果filterData有，不能筛选，只能是filterData的值
                                childFilterData[v.name] = Object.assign(childFilterData[v.name], v.filterData);
                            }
                        })
                    }

                    if (this.showMoreFilter === false && haveHide) {
                        this.showMoreFilter = true;
                    }

                    return {
                        filterData, childFilterData
                    }
                },
                moreShowItems(items) {
                    if (!items) {
                        return [];
                    }
                    if (!this.filterValues) {
                        return items;
                    }
                    return items.filter(vo => {
                        return !this.filterValues[vo.name];
                    })
                }
            },
            template: `<div class="curd-filter-box">
                    <a-spin :spinning="loading">
                            <div class="filter-box-title" v-if="childs&&childs.length>0&&filterGroupIsShow(base)">{{title}}：</div>
                            <div class="filter-box-div" v-if="filterGroupIsShow(base)">
                                <transition-group name="bounce">
                                    <template v-for="(item,index) in filterSource.filterConfig">
                                        <div class="filter-item-box" v-if="item.show&&(!filterValues||!filterValues[item.name])" :key="item.name">
                                            <div class="filter-item"><div class="filter-item-l">{{item.title}}</div> <div class="filter-item-r">
                                             <component :is="item.type" 
                                                        :config="item"
                                                        :ref="'filters.filterConfig.'+item.name"
                                                        @search="search($event,item)"
                                                ></component>
                                            </div></div>
                                        </div>
                                    </template>
                                </transition-group>
                            </div>
                            <template v-if="childs">
                                <template v-for="child in childs">
                                    <div class="filter-box-title" v-show="filterGroupIsShow(child)">{{child.title}}：</div>
                                    <div class="filter-box-div" v-show="filterGroupIsShow(child)">
                                        <transition-group name="bounce">
                                            <template v-for="(item,index) in filterSource[child.name]" :key="item.name">
                                                <div class="filter-item-box" v-if="filterGroupItemIsShow(item,child)">
                                                    <div class="filter-item"><div class="filter-item-l">{{item.title}}</div> <div class="filter-item-r">
                                                     <component :is="item.type" 
                                                                :config="item"
                                                                :ref="'filters.'+child.name+'.'+item.name"
                                                                @search="search($event,item)"
                                                        ></component>
                                                    </div></div>
                                                </div>
                                            </template>
                                        </transition-group>
                                    </div>
                                </template>
                            </template>
                        </a-spin>
                        <div class="filter-sub-btn-box">
                            <a-divider v-if="showMoreFilter">
                                <a-dropdown trigger="click">
                                    <a class="ant-dropdown-link" style="font-size: 14px"> 更多筛选
                                        <down-outlined></down-outlined>
                                    </a>
                                    <template #overlay>
                                        <a-menu id="filter-menu-box">
                                            <template v-for="(vo,key) in filterSource">
                                            <template v-if="moreShowItems(vo).length>0">
                                                <div v-if="modelTitles[key]" class="filter-select-show-item-title">{{modelTitles[key]}}</div>
                                                <div class="filter-select-show-item-box">
                                                    <a-menu-item v-for="item in moreShowItems(vo)">
                                                        <a href="javascript:;"
                                                           class="filter-select-show-item"
                                                           :class="{checked:item.show}"
                                                           @click="item.show=!item.show">
                                                            <div class="filter-select-show-title">{{ item.title }}</div>
                                                            <check-outlined></check-outlined>
                                                        </a>
                                                    </a-menu-item>
                                                </div>
                                            </template>
                                            </template>
                                        </a-menu>
                                    </template>
                                </a-dropdown>
                            </a-divider>
                        </div>
                    </div>`,
        });

        for (let componentName in fieldComponents) {
            app.component(componentName, typeof require(fieldComponents[componentName]) === 'function' ? require(fieldComponents[componentName])() : require(fieldComponents[componentName]))
        }
        for (let componentName in filterComponents) {
            app.component(componentName, require(filterComponents[componentName]));
        }
        app.mount('#app')
    };
});