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
    //防抖
    function debounce(fn, delay) {
        let timer = 0
        return function() {
            // 如果这个函数已经被触发了
            if(timer){
                clearTimeout(timer)
            }
            timer = setTimeout(() => {
                fn.apply(this, arguments); // 透传 this和参数
                timer = 0
            },delay)
        }
    }

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
        // baseURL: '/' + window.VUE_CURD.MODULE + '/',
        withCredentials: true,
        timeout: 150000
    });
    service.interceptors.response.use(async response => {
        const res = response.data;
        if(response.request.responseType==='arraybuffer'||response.request.responseType==='blob'){
            // 这里 data 是返回来的二进制数据
            const blob = new Blob([response.data], response.headers);
            // 创建一个blob的对象链接
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            const match=response.headers['content-disposition'].match(/;?\s*filename="?([^;]+)"?/);
            // 把获得的blob的对象链接赋值给新创建的这个 a 链接
            link.setAttribute('download', match[1]?.trim()?.replace(/"/g,'')||'下载'); // 设置下载文件名
            document.body.appendChild(link);
            // 使用js点击这个链接
            link.click();
            setTimeout(()=>{
                document.body.removeChild(link) // 下载完成移除元素
                window.URL.revokeObjectURL(url) // 释放blob对象
            })
            return Promise.reject(res)
        }
        if (parseInt(res.code) === 1) {
            return res
        }
        if (res.confirm && res.confirm.show) {
            return await new Promise((resolve, reject) => {
                (top.ArcoVue||ArcoVue).Modal.warning({
                    title: Vue.createVNode('b', {}, res.confirm.title),
                    content: res.msg, okText: res.confirm.okText, cancelText: res.confirm.cancelText,
                    hideCancel:false,
                    onOk() {
                        response.config.headers['confirm-error-code'] = res.errorCode;
                        resolve(service(response.config))
                    }, onCancel() {
                        reject({code: 0, msg: '已取消执行', data: [],})
                    }
                });
            })
        }


        (top.ArcoVue||ArcoVue).Message.error(res.msg || '失败');
        if (res.url && res.url.indexOf(vueData.loginUrl) !== -1) {
            (top.ArcoVue||ArcoVue).Modal.confirm({
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
            let msg='发生错误';
            if(error.response&&error.response.data&&typeof error.response.data.message==='string'){
                msg+='：'+error.response.data.message
            }
            error = {code: 0, msg:msg, data: [],}
        }
        ArcoVue.Message.error({
            content:error.msg,
            duration: 6*1000
        });
        return Promise.reject(error)
    })


    /****窗口方法***/
    window.parseTime = function (time, cFormat) {
        if (arguments.length === 0) {
            return null
        }
        const format = cFormat || '{y}-{m}-{d} {h}:{i}:{s}';
        let date;
        if (typeof time === 'object') {
            date = time
        } else {
            if ((typeof time === 'string') && (/^-?[0-9]+$/.test(time))) {
                time = parseInt(time)
            }
            // if ((typeof time === 'number') && (time.toString().length === 10 || time.toString().length === 9)) {
            //     time = time * 1000
            // }
            date = new Date(time)
        }
        const formatObj = {
            y: date.getFullYear(),
            m: date.getMonth() + 1,
            d: date.getDate(),
            h: date.getHours(),
            i: date.getMinutes(),
            s: date.getSeconds(),
            a: date.getDay()
        };
        return format.replace(/{(y|m|d|h|i|s|a)+}/g, (result, key) => {
            let value = formatObj[key];
            if (key === 'a') {
                return ['日', '一', '二', '三', '四', '五', '六'][value]
            }
            if (result.length > 0 && value < 10) {
                value = '0' + value
            }
            return value || 0
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
                let history=[];
                let historyIndex=0;
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
                        let iframe = layero.find('iframe')[0];
                        let navigationType=iframe.contentWindow.performance.getEntriesByType("navigation")[0].type;
                        if(navigationType==='navigate'||navigationType==='prerender'){
                            //正常跳转
                            if(history.length===0){
                                historyIndex=0;
                            }else{
                                history=history.slice(0,historyIndex+1)
                                historyIndex++;
                            }
                            history.push(iframe.contentWindow.location.href);
                        }else if(navigationType==='back_forward'){
                            //后退或者前进
                            historyIndex=history.indexOf(iframe.contentWindow.location.href)
                        }
                        iframe.contentWindow.getHistory=function (){return {list:history,index:historyIndex};};



                        let body = box.layui.layer.getChildFrame('body', index);
                        layero.css('overflow', 'hidden');
                        iframe.contentWindow.listVue = vueObj;//将当前页面的this保存到新页面的window里面
                        iframe.contentWindow.parentWindow = window;
                        layero.close = function () {
                            box.layui.layer.close(index);
                        }
                        if (body.length > 0) {
                            body.attr('layer-index', index);
                            body.on('closeIframe', function () {
                                layero.close();
                            })
                            layero.find('iframe').css('padding', '0px');
                        }
                        trigger('success', layero, index);
                        if(iframe.contentWindow.iframeLoad){
                            iframe.contentWindow.iframeLoad(layero, index);
                        }
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
            if(box.appPage&&!box.appParam){
                box.appParam=box.appPage;
            }
            if (!box.appParam.bodyModals || !box.appParam.bodyDrawers) {
                box.appParam = box.appPage;
            }
            let appObj = box.appParam;
            //如果不是iframe,打于开当前页面
            return MyPromise(function (trigger) {
                let key;
                const openInfo = {
                    visible: false,
                    closable:true,
                    width:option.area?option.area[0]:undefined,
                    height:option.area?option.area[1]:undefined,
                    title:option.title||undefined,
                    url:option.url||option.content||undefined,
                    zIndex:option.zIndex||undefined,
                };
                if ((option.offset && option.offset === 'auto')||(openInfo.height&&!option.offset&&openInfo.height!=='100vh'&&openInfo.height!=='100%')) {
                    key = 'bodyModals';
                } else {
                    key = 'bodyDrawers';
                    switch (option.offset||'rt') {
                        case 'l':
                        case 'lt':
                        case 'lb':
                            openInfo.offset = 'left';
                            break;
                        case 'r':
                        case 'rt':
                        case 'rb':
                            openInfo.offset = 'right';
                            break;
                        case 't':
                            openInfo.offset = 'top';
                            break;
                        case 'b':
                            openInfo.offset = 'bottom';
                            break;
                        default:
                            openInfo.offset = 'right';
                    }
                    openInfo.placement = openInfo.offset;
                    if((openInfo.placement==='left'||openInfo.placement==='right')&&typeof openInfo.width==='undefined'){
                        openInfo.width='45vw';
                    }else if((openInfo.placement==='top'||openInfo.placement==='bottom')&&typeof openInfo.height==='undefined'){
                        openInfo.height='45vh';
                    }
                }
                this.openType = key;
                appObj[key]=appObj[key]||[];
                this.openIndex = appObj[key].length;
                openInfo.onclose = function () {
                    trigger('close');
                }
                openInfo.onBeforeClose = ()=> {
                    let iframe =  appObj[this.openType][this.openIndex].iframe;
                    if(iframe.contentWindow.onOpenInfoBeforeClose){
                        iframe.contentWindow.onOpenInfoBeforeClose(appObj[this.openType][this.openIndex]);
                    }
                }

                let history=[];
                let historyIndex=0;
                openInfo.onload = (e) => {
                    let iframe = e.target;
                    appObj[this.openType][this.openIndex].iframe=iframe;
                    iframe.contentWindow.openInfo= appObj[this.openType][this.openIndex];
                    
                    let navigationType=iframe.contentWindow.performance.getEntriesByType("navigation")[0].type;
                    if(navigationType==='navigate'||navigationType==='prerender'){
                        //正常跳转
                        if(history.length===0){
                            historyIndex=0;
                        }else{
                            history=history.slice(0,historyIndex+1)
                            historyIndex++;
                        }
                        history.push(iframe.contentWindow.location.href);
                    }else if(navigationType==='back_forward'){
                        //后退或者前进
                        historyIndex=history.indexOf(iframe.contentWindow.location.href)
                    }
                    iframe.contentWindow.getHistory=function (){return {list:history,index:historyIndex};};


                    let body = iframe.contentWindow.document.querySelector('body');
                    iframe.contentWindow.listVue = vueObj;//将当前页面的this保存到新页面的window里面
                    iframe.contentWindow.parentWindow = window;
                    let paramData = {
                        iframe,
                        body,
                        option: openInfo,
                        close: () => {
                            appObj[this.openType][this.openIndex].visible = false;
                            // openInfo.visible = false;
                        }
                    };
                    let myEvent = new Event("closeIframe", {option: openInfo});
                    body.addEventListener("closeIframe", e => {
                        paramData.close()
                    });
                    trigger('success', paramData);
                    if(iframe.contentWindow.iframeLoad){
                        iframe.contentWindow.iframeLoad(e);
                    }
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
                        (top.ArcoVue||ArcoVue).Notification.error({title:'失败',content: '服务器发生错误'});
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
                        (top.ArcoVue||ArcoVue).Notification.error({title:'失败',content:data.msg});
                        if ($error) {
                            $error(data);
                        }
                    }
                }
            }

            function uploadFailed(evt) {
                let msg = '上传文件发生了错误尝试!';
                (top.ArcoVue||ArcoVue).Notification.error({title:'失败',content:msg});
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
                (top.ArcoVue||ArcoVue).Notification.error({title:'失败',content:msg});
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
            if (window.VUE_CURD.MODULE&&url.indexOf('/' + window.VUE_CURD.MODULE + '/') !== 0&&/^\/?\w+\.php/.test(url)===false&&/^https?:/.test(url)===false) {
                url = '/' + window.VUE_CURD.MODULE + '/'+url;
            }
            const option={url, method: 'get', params, headers: {'X-REQUESTED-WITH': 'xmlhttprequest'}};
            if(/[?&]curd_download=1/.test(url)||url.indexOf('/curd_download/1')>-1){
                option.responseType='arraybuffer';
            }
            return service(option)
        },
        '$post'(url, data) {
            if (window.VUE_CURD.MODULE&&url.indexOf('/' + window.VUE_CURD.MODULE + '/') !== 0&&/^\/?\w+\.php/.test(url)===false&&/^https?:/.test(url)===false) {
                url = '/' + window.VUE_CURD.MODULE + '/'+url;
            }

            const option={
                url,
                method: 'post',
                data: typeof data === 'undefined'||typeof data.append==='function'?data:Qs.stringify(data),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    'X-REQUESTED-WITH': 'xmlhttprequest'
                }
            };
            if(/[?&]curd_download=1/.test(url)||url.indexOf('/curd_download/1')>-1){
                option.responseType='arraybuffer';
            }
            return service(option)
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
                        document.querySelectorAll('#vue-curd-imgs-show-box .arco-image-img')[index].click()
                    })
                }
            }
        },
        showImages(imgs, start) {
            window.top.showImages(imgs, start);
        },
        log(obj) {
            return console.log(obj);
        },
        openOtherBtn(btn, row) {
            let w = (btn.modalW || '45vw').toLowerCase();
            let h = (btn.modalH || '100vh').toLowerCase();

            let offset = btn.modalOffset;
            if (!offset) {
                offset = h === '100vh' ? 'rt' : 'auto';
            }
            if (btn.selfType === 'OpenBtn') {
                this.openBox({
                    title: btn.modalTitle,
                    offset: offset,
                    area: [w, h],
                    content: btn.modalUrl,
                }).end();
                return;
            }


            if (!btn.modalFields) {
                (top.ArcoVue||ArcoVue).Modal.warning({
                    title: Vue.createVNode('b', {}, '您确定要执行此操作吗？'),
                    content: btn.modalTitle,
                    hideCancel:false,
                    onBeforeOk:()=> {
                        return new Promise((resolve, reject) => {
                            let option = {};
                            if (row) {
                                option.id = row.id;
                            }
                            this.$post(btn.saveUrl, option).then(res => {
                                if (btn.refreshPage) {
                                    if (top.layer && top.layer.msg) {
                                        top.layer.msg(res.msg, {
                                            icon: 1,
                                            shade: this.shade,
                                            scrollbar: false,
                                            time: 1500,
                                            shadeClose: true
                                        })
                                        window.vueDefMethods.showLoadMsg('', window.document.querySelector('body'))
                                        window.setTimeout(() => {
                                            window.location.reload();
                                        }, 80)
                                    } else {
                                        (top.ArcoVue||ArcoVue).Message.success(res.msg);
                                        window.vueDefMethods.showLoadMsg('', window.document.querySelector('#app>.box>.body'))
                                        window.setTimeout(() => {
                                            window.location.reload();
                                        }, 200)
                                    }
                                    resolve()
                                    return;
                                }
                                (top.ArcoVue||ArcoVue).Message.success(res.msg);
                                if (btn.refreshList) {
                                    this.refreshTable();
                                } else if (row) {
                                    this.refreshId(row.id)
                                }
                                resolve()
                            }).catch(err => {
                                reject(err)
                            })
                        });
                    }
                });

                return;
            }
            this.openBox({
                title: btn.modalTitle,
                area: [w, h],
                offset: offset,
                content: '/tpscriptvuecurd/row_other_btn/show_inputs.vue?'+(window.VUE_CURD.DEBUG?'t='+(new Date()).getTime():'v='+(window.VUE_CURD.VERSION||'')),
            }).on('success', function (layero) {
                const iframe = layero.iframe ? layero.iframe : layero.find('iframe')[0];
                const win = iframe.contentWindow;

                function runScript(script) {
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

                function setHTMLWithScript(container, rawHTML) {
                    container.innerHTML = rawHTML;
                    const scripts = container.querySelectorAll('script');

                    return Array.prototype.slice.apply(scripts).reduce((chain, script) => {
                        return chain.then(() => runScript(script));
                    }, Promise.resolve());
                }

                win.VUE_CURD = window.VUE_CURD;
                win.thatBtn = btn;
                win.beforeInit = function () {
                    win.vueData.title = btn.modalTitle;
                    win.vueData.fields = btn.modalFields;
                    win.vueData.groupFields = btn.modalGroupFields;
                    win.vueData.groupGrids = btn.modalGroupGrids;
                    win.vueData.fieldComponents = btn.modalFieldsComponents;
                    win.vueData.isStepNext = false;
                    win.vueData.stepInfo = null;
                    win.vueData.vueCurdAction = 'edit';

                    win.vueData.info = btn.info && Object.keys(btn.info).length > 0 ? btn.info : (row ? {id: row.id} : {});
                    win.vueData.subUrl = btn.saveUrl;
                    win.vueData.subBtnTitle = btn.saveBtnTitle;
                }

                let headHtml = '';
                let headEls = document.querySelector('head').children;
                for (let i in headEls) {
                    if (typeof headEls[i].getAttribute === 'function' && !headEls[i].getAttribute('data-requiremodule')) {
                        headHtml += headEls[i].outerHTML;
                    }
                }
                setHTMLWithScript(win.document.querySelector('head'), "<style id='init-before-style'>body{display: none}</style>"
                    + headHtml
                    + "<script src=\"/tpscriptvuecurd/require-2.3.6/require.js\" charset=\"utf-8\"></script>"
                    + "<script src=\"/tpscriptvuecurd/require-config.js\" charset=\"utf-8\"></script>"
                    + "<script>window.beforeInit();setTimeout(()=>{document.querySelector('#init-before-style').remove();require(['/tpscriptvuecurd/row_other_btn/show_inputs.js']);},100);"
                    + "</script>");


            }).end();

        }
    };


    return function (option) {
        option.data = option.data || function () {
            return {};
        };
        let dt = option.data();
        dt.bodyDrawers = [];
        dt.bodyModals = [];
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
        app.use(ArcoVue);
        app.use(ArcoVueIcon);



        /****************************************/
        const checkVal = function (fieldWhere, val) {
            if (fieldWhere.type === 'in') {
                for (let i in fieldWhere.valueData) {
                    let whereVal=fieldWhere.valueData[i];
                    if(whereVal===undefined||whereVal===null){
                        whereVal='';
                    }else{
                        whereVal=whereVal.toString();
                    }
                    if (whereVal === val.toString()) {
                        return true;
                    }
                }
                return false;
            }
            if (fieldWhere.type === 'find_in_set') {
                if (val === '') val = [];
                const valArr = typeof val === 'object' ? val : val.toString().split(',');
                for (let i in valArr) {
                    for (let n in fieldWhere.valueData) {
                        if (fieldWhere.valueData[n].toString() === valArr[i]) {
                            return true;
                        }
                    }
                }
                return false;
            }


            if (typeof val === 'string') {
                val = parseFloat(val);
            }

            const minVal = typeof fieldWhere.valueData[0] === 'string' ? parseFloat(fieldWhere.valueData[0]) : fieldWhere.valueData[0];
            const maxVal = typeof fieldWhere.valueData[1] === 'string' ? parseFloat(fieldWhere.valueData[1]) : fieldWhere.valueData[1];

            if (minVal === null) {
                return val <= maxVal;
            }

            if (maxVal === null) {
                return val >= minVal;
            }

            return val >= minVal && val <= maxVal;
        }
        const checkFieldWhereSelf = (fieldWhere,formVal,info) => {
            if (fieldWhere.field.name === fieldWhere.RETURN_FALSE_FIELD_NAME) {
                return fieldWhere.ands && fieldWhere.ands.length > 0;
            }
            let val = null;
            let formValue = formVal[fieldWhere.field.name];
            if (fieldWhere.field.type === 'FilesField'
                && fieldWhere.field.editShow === false&&info
                && info.sourceData && typeof info.sourceData[fieldWhere.field.name] !== 'undefined') {
                formValue = info.sourceData[fieldWhere.field.name];
            }
            if (typeof formValue === 'undefined') {
                if (!info || typeof info[fieldWhere.field.name] === 'undefined') {
                    return !fieldWhere.isNot;
                }
                val = info[fieldWhere.field.name];
            } else {
                val = formValue;
            }
            if (val === null) {
                return !fieldWhere.isNot;
            }

            if (val) {
                if (fieldWhere.field.type === 'DateField' || fieldWhere.field.type === 'MonthField' || fieldWhere.field.type === 'WeekField') {
                    if (!/^\d+$/.test(val.toString())) {
                        const d=new Date(val);
                        if(!fieldWhere.field.showTime){
                            d.setHours(0,0,0)
                        }
                        val = d.getTime()/1000;
                    }
                }
            }
            return checkVal(fieldWhere, val) !== fieldWhere.isNot;
        };
        const checkFieldWhere = function (fieldWhere,formVal,info) {
            let check = checkFieldWhereSelf(fieldWhere,formVal,info);
            if (check) {
                for (let i in fieldWhere.ands) {
                    if (checkFieldWhere(fieldWhere.ands[i],formVal,info) === false) {
                        check = false;
                        break;
                    }
                }
            }
            if (check) {
                return true;
            }
            for (let i in fieldWhere.ors) {
                if (checkFieldWhere(fieldWhere.ors[i],formVal,info)) {
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


        /*************************************/

        app.component('FieldGroupItem', {
            name: 'fieldGroupItem',
            props: ['groupFieldItems', 'form', 'listFieldLabelCol', 'listFieldWrapperCol', 'fieldHideList', 'info','grid'],
            setup(props, ctx) {
                return {
                    formVal: Vue.ref(props.form),
                    validateStatus: Vue.ref({}),
                    formValIsImmediateed:Vue.ref(false),
                    formValOld:Vue.ref({}),
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
                },
                gridStyle(){
                    const style={};
                    if(!this.grid){
                        return style;
                    }

                    for(let i in this.grid){
                        if(this.grid[i]){
                            style[i]=this.grid[i];
                        }
                    }
                    if(Object.keys(style).length>0){
                        style.display='grid';
                    }
                    return style;
                },
            },
            watch: {
                formVal: {
                    handler(formVal,formValOld) {
                        let forceUpdate=this.fieldChangeDo(formVal);
                        this.$emit('update:form', formVal);
                        if(forceUpdate){
                            this.updateFormView=this.updateFormView||1;
                            this.updateFormView++;
                            let updateFormView=this.updateFormView;
                            this.$nextTick(()=>{
                                if(updateFormView===this.updateFormView){
                                    this.$forceUpdate();
                                }
                            })
                        }



                        if(this.formValIsImmediateed===false){
                            this.formValIsImmediateed=true;
                            this.formValOld=JSON.parse(JSON.stringify(formVal));
                        }else{
                            if(this.odFormChangeSet){
                                this.odFormChangeSet(formVal,this.formValOld);
                            }
                        }
                    },
                    immediate: true,
                    deep: true,
                },

            },
            mounted(){
                this.odFormChangeSet=debounce((formVal,formValOld)=>{
                    this.$nextTick(()=>{
                        this.formChangeSet(formVal,formValOld);
                    });
                },80);
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
                        //ListField
                        if (isNotErr && field.fields&&field.fields.length>0) {
                            for (let i in this.form[field.name]) {
                                if (this.$refs['listFieldForm' + i]) {
                                    isNotErr = await new Promise(resolve => {
                                        this.$refs['listFieldForm' + i].validate(errors=>{
                                            if(errors&&Object.keys(errors).length>0){
                                                (top.ArcoVue||ArcoVue).Message.warning(errors[Object.keys(errors)[0]].message);
                                                console.log('error', error);
                                                resolve(false);
                                            }else{
                                                resolve(true);
                                            }
                                        })
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
                fieldStyle(field){
                    const style={};
                    if(!field.grid||!this.grid){
                        return style;
                    }
                    for(let i in field.grid){
                        if(field.grid[i]){
                            style[i]=field.grid[i];
                        }
                    }
                    return style;
                },
                formChangeSet(formVal,formValOld){
                    this.groupFieldItems.forEach(field => {
                        if(!field.editOnChange){
                            return;
                        }
                        let oldVal=typeof formValOld[field.name]==='undefined'||formValOld[field.name]===null?'':formValOld[field.name].toString();
                        let newVal=typeof formVal[field.name]==='undefined'||formVal[field.name]===null?'':formVal[field.name].toString();
                        if(oldVal!==newVal){
                            if(field.editOnChange.type==='Url'||field.editOnChange.type==='Func'){
                                this.ajaxGetFormChangeSet(field.editOnChange.url,field,oldVal);
                            }else if(field.editOnChange.type==='KeyVal'){
                                if(field.editOnChange.where===null||checkFieldWhere(field.editOnChange.where,formVal,this.info)){
                                    const ks=field.editOnChange.key.split('.');
                                    let rsdata=field.editOnChange.val,rsdata2={};
                                    for(let i=ks.length-1;i>=0;i--){
                                        rsdata2[ks[i]]=rsdata;
                                        rsdata=rsdata2;
                                        rsdata2={};
                                    }
                                    this.doChangeSet({data:rsdata});
                                }
                            }
                        }
                    })
                    this.formValOld=JSON.parse(JSON.stringify(formVal));
                },
                '$post': vueDefMethods.$post,
                ajaxGetFormChangeSet(url,field,oldVal){
                    this.$post(url,{
                        formChangeSetField:field.name,
                        pageGuid:VUE_CURD.GUID,
                        oldVal,
                        form:this.formVal,
                        id:this.formVal.id||0,
                        base_id:this.formVal.base_id||0,
                    }).then(res=>{
                        this.doChangeSet(res);
                    })
                },
                doChangeSet(res){
                    let fieldKeys={};
                    this.groupFieldItems.forEach((field,key) => {
                        fieldKeys[field.name]=key;
                    });
                    let updateFormView=false;
                    if(res.data.fields&&typeof res.data.fields==='object'){
                        for(let fieldName in res.data.fields){
                            for (let key in res.data.fields[fieldName]){
                                if(typeof fieldKeys[fieldName]!=='undefined'){
                                    updateFormView=true;
                                    let val=typeof res.data.fields[fieldName][key]==='number'?res.data.fields[fieldName][key].toString():res.data.fields[fieldName][key];
                                    this.groupFieldItems[fieldKeys[fieldName]].attrWhereValueList[key]=this.groupFieldItems[fieldKeys[fieldName]].attrWhereValueList[key]||[];
                                    this.groupFieldItems[fieldKeys[fieldName]].attrWhereValueList[key].push({
                                        value:val,
                                        where:null
                                    })
                                    this.groupFieldItems[fieldKeys[fieldName]][key]=val;
                                }
                            }
                        }
                    }
                    if(res.data.form){
                        for(let key in res.data.form){
                            updateFormView=true;
                            this.formVal[key]=typeof res.data.form[key]==='number'?res.data.form[key].toString():res.data.form[key];
                        }
                    }

                    if(updateFormView===true){
                        this.fieldChangeDo();
                        this.$nextTick(()=>{
                            this.$forceUpdate();
                        })
                    }
                },
                fieldChangeDo(formVal){
                    let forceUpdate=false;
                    formVal=formVal||this.formVal;
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    const checkShowItemBy = function (field) {
                        if (!field.items || field.items.length === 0) {
                            return;
                        }
                        //TODO::未完成对TreeSelect的支持
                        field.items.forEach(v => {
                            if (!v.showItemBy) {
                                if(typeof v.showItem!=='undefined'){
                                    delete v.showItem;
                                    forceUpdate=true;
                                }
                                return;
                            }
                            if (checkFieldWhere(v.showItemBy,formVal,this.info)) {
                                if(v.showItem!==true){
                                    v.showItem = true;
                                    forceUpdate=true;
                                }
                            } else {
                                if (formVal[field.name] !== undefined && formVal[field.name] !== '') {
                                    if (field.type === 'CheckboxField' || ((field.type === 'SelectField' || field.type === 'TreeSelect') && field.multiple)) {
                                        let newVals = [];
                                        formVal[field.name].toString().split(',').forEach(val => {
                                            if (val !== v.value.toString()) {
                                                newVals.push(val);
                                            }
                                        })
                                        const newVal = newVals.join(',');
                                        if (newVal !== formVal[field.name]) {
                                            formVal[field.name] = this.formVal[field.name] = newVal;
                                            forceUpdate=true;
                                        }
                                    } else if (formVal[field.name].toString() === v.value.toString()) {
                                        formVal[field.name] = this.formVal[field.name] = '';
                                        forceUpdate=true;
                                    }
                                }

                                if(v.showItem!==false){
                                    v.showItem = false;
                                    forceUpdate=true;
                                }

                            }
                        });
                    };
                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    const checkFieldEditTipShow = function (field) {
                        if (field.editTips.length === 0) {
                            if(field.editTipArr&&field.editTipArr.length>0){
                                forceUpdate=true;
                            }
                            field.editTipArr = [];
                            return;
                        }
                        let old=JSON.stringify(field.editTipArr);
                        field.editTipArr = field.editTips.filter(val => val.show === null ? true : checkFieldWhere(val.show,formVal,this.info));
                        if(forceUpdate===false&&old!==JSON.stringify(field.editTipArr)){
                            forceUpdate=true;
                        }
                    }

                    ///////////////////////////////////////////////////////////////////////////////////////////////
                    const checkFieldTipShow = function (field) {
                        if (field.tips.length === 0) {
                            if(field.tipArr&&field.tipArr.length>0){
                                forceUpdate=true;
                            }
                            field.tipArr = [];
                            return;
                        }
                        let old=JSON.stringify(field.tipArr);
                        field.tipArr = field.tips.filter(val => val.show === null ? true : checkFieldWhere(val.show,formVal,this.info));
                        if(forceUpdate===false&&old!==JSON.stringify(field.tipArr)){
                            forceUpdate=true;
                        }
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
                            forceUpdate=true;
                            return;
                        }
                        if (typeof this.currentFieldHideList[key] === 'undefined') {
                            return;
                        }
                        if (this.currentFieldHideList[key].length > 0) {
                            let old=JSON.stringify(this.currentFieldHideList[key]);
                            this.currentFieldHideList[key] = this.currentFieldHideList[key].filter(v => v !== fieldName);
                            if(forceUpdate===false&&old!==JSON.stringify(this.currentFieldHideList[key])){
                                forceUpdate=true;
                            }
                        }
                        if (this.currentFieldHideList[key].length === 0) {
                            delete this.currentFieldHideList[key]
                            forceUpdate=true;
                        }
                    }
                    const checkHideField = (field, checkVal) => {
                        if (field.hideSelf) {
                            changeFieldHideList(field.name, getWhereFields(field.hideSelf).join(','), checkFieldWhere(field.hideSelf,formVal,this.info));
                        }

                        let reversalHideFields = !!field.reversalHideFields,
                            oldHideFields = Object.keys(this.currentFieldHideList);
                        if (field.hideFields) {
                            let allFields = [], hideFileds = [], inputVal = '';
                            if (checkVal !== '') {
                                inputVal = checkVal || '';
                                //如果是时间格式
                                if (field.type === 'DateField' || field.type === 'MonthField' || field.type === 'WeekField') {
                                    if (!/^\d+$/.test(checkVal.toString())) {
                                        const d=new Date(checkVal);
                                        if(!field.showTime){
                                            d.setHours(0,0,0)
                                        }
                                        inputVal = d.getTime()/1000;
                                    }
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
                    const setFieldAttrByWhere = (field) => {
                        const def = '--setAttrValByWheres--def--';
                        for (const attr in field.attrWhereValueList) {
                            let val = typeof field[attr] === 'undefined' ? def : field[attr];
                            for (let k = field.attrWhereValueList[attr].length - 1; k >= 0; k--) {
                                const {value, where} = field.attrWhereValueList[attr][k];
                                if (where === null || checkFieldWhere(where,formVal,this.info)) {
                                    val = value;
                                    break;
                                }
                            }
                            if ((typeof field[attr] === 'undefined' && val !== def) || field[attr] !== val) {
                                field[attr] = val;
                                forceUpdate=true;
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

                    return forceUpdate;
                },
                strNToArr(str){
                    if(str===''||typeof str!=='string'){
                        return [];
                    }
                    return str.split(/\n/).filter(v=>v!=='');
                }
            },
            template: `
                        <div :style="gridStyle">
                            <div v-for="field in groupFieldItems" :data-name="field.name" :style="fieldStyle(field)">
                                <transition name="slide-fade">
                                <a-form-item class="curd-form-item" v-if="field.editShow" v-show="triggerShows(field.name)" :label="fieldLabel(field)" :hide-label="fieldLabel(field)===''" :field="field.name" :rules="fieldRules(field)" :validate-status="validateStatus[field.name]" :label-col-props="field.editLabelCol" :wrapper-col-props="field.editWrapperCol" :label-col-style="{'justify-content':field.editLabelAlign==='left'?'flex-start':'flex-end'}" :show-colon="field.editColon" class="form-item-row">
                                    <div :class="{'field-tips':true,'field-tips-have-items':field.editTipArr&&field.editTipArr.length>0}">
                                        <transition-group name="to-right"><a-alert class="field-tips-item" v-for="item in field.editTipArr" :key="item.guid" :title="item.title" :banner="!item.border" :closable="item.closable" :show-icon="item.showIcon" :type="item.type">{{item.message}}</a-alert></transition-group>
                                    </div>
                                    <div :class="{'field-tips':true,'field-tips-have-items':field.tipArr&&field.tipArr.length>0}">
                                        <transition-group name="to-right"><a-alert class="field-tips-item" v-for="item in field.tipArr" :key="item.guid"  :title="item.title" :banner="!item.border" :closable="item.closable" :show-icon="item.showIcon" :type="item.type">{{item.message}}</a-alert></transition-group>
                                    </div>
                                    <div style="flex: 1"><component 
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
                                    ></component></div>
                                    <transition name="to-right">
                                    <div v-if="field.editExplain" :style="{color:field.editExplainColor}">
                                        <template v-for="(v,i) in strNToArr(field.editExplain)"><br v-if="i>0"/>{{v}}</template>
                                    </div>
                                    </transition>
                                    <transition name="to-right">
                                    <div v-if="field.explain" :style="{color:field.explainColor}">
                                         <template v-for="(v,i) in strNToArr(field.explain)"><br v-if="i>0"/>{{v}}</template>
                                    </div>
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
            computed: {
                tipArr() {
                    if (this.field.tips.length === 0) {
                        return [];
                    }
                    const formVal={};
                    for(let i in this.info){
                        formVal[i]=this.info[typeof this.info['_Original_'+i]==='undefined'?i:'_Original_'+i];
                    }
                    if(typeof this.info.sourceData==='undefined'){
                        this.info.sourceData=formVal;
                    }
                    return this.field.tips.filter(val => val.show === null ? true : checkFieldWhere(val.show,formVal,this.info));
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
            props: ['childs', 'pagination', 'data', 'loading', 'listColumns', 'canAdd', 'canEdit', 'actionWidth', 'canDel', 'rowSelection','selectedKeys', 'fieldStepConfig', 'actionDefWidth', 'showCreateTime', 'setScrollY', 'childrenColumnName', 'indentSize', 'expandAllRows', 'isTreeIndex','showAction'],
            setup(props, ctx) {
                const guid=window.guid();
                Vue.provide('table-guid',guid)

                const newActionW =Vue.ref(0);
                const columnsVals = Vue.ref([]);
                const isGroup=Vue.ref(false);
                const titleItems=Vue.ref({});
                const listFieldComponents=Vue.ref({});
                const fieldObjs=Vue.ref({});
                const childsObjs=Vue.ref({});

                const scrollX = Vue.ref(undefined);
                const scrollY = Vue.ref(undefined);
                const id = 'pub-default-table-' + guid;
                let onresize;
                const oldResize = window.onresize || function () {};

                Vue.watchEffect(()=>{
                    const listColumns = props.listColumns;
                    let groupTitles = [], columns = [], ti = {}, columnsCount = 0, lfc = {},fo = {};
                    for (let groupTtitle in listColumns) {
                        groupTitles.push(groupTtitle);
                        let column = {title: groupTtitle, children: []};
                        listColumns[groupTtitle].forEach(function (item) {
                            fo[item.name] = item;
                            let customTitle = 'custom-title-' + item.name;
                            ti[customTitle] = item;
                            let col = {
                                dataIndex: item.name,
                                name:item.name,
                                // title:item.title,
                                titleSlotName: customTitle,
                                fixed: item.listFixed ? item.listFixed : false,
                            };
                            if(!(item.listEdit&&item.listEdit.saveUrl)){
                                col.ellipsis=true;
                                col.tooltip=true;
                            }
                            if (fieldComponents['VueCurdIndex' + item.type]) {
                                lfc[item.name] = item;
                                col.slotName = 'field-component-' + item.name;
                            } else {
                                col.slotName = 'default-value';
                            }

                            if (item.listColumnWidth) {
                                col.width = item.listColumnWidth;
                            }
                            if(item.listSort){
                                col.sortable={
                                    sortDirections: ['ascend', 'descend']
                                };
                            }
                            columnsCount++;
                            column.children.push(col);
                        })
                        if(groupTtitle===''||groupTtitle==='基本信息'){
                            columns.push(...column.children);
                        }else{
                            columns.push(column);
                        }
                    }
                    isGroup.value = groupTitles.length > 1 || (!listColumns[''] &&!listColumns['基本信息'] && groupTitles.length > 0);

                    if (!isGroup.value && columns[0]) {
                        columns = columns[0].children?columns[0].children:columns;
                    }


                    const createTimeCol = {
                        // title:'创建时间',
                        ellipsis: true,
                        tooltip: true,
                        dataIndex: "create_time",
                        titleSlotName:'custom-title-create_time',
                        slotName:'create-time',
                        width: 152,
                        sortable: {
                            sortDirections: ['ascend', 'descend']
                        },
                    };

                    if (props.fieldStepConfig && props.fieldStepConfig.enable && props.fieldStepConfig.listShow === true) {
                        const stepCol = {
                            // title:'当前步骤',
                            ellipsis: true,
                            dataIndex: "stepInfo",
                            titleSlotName:'custom-title-step-info',
                            slotName:'step-info',
                        };
                        if(props.fieldStepConfig.listFixed){
                            stepCol.fixed=props.fieldStepConfig.listFixed;
                        }

                        if (props.fieldStepConfig.width && props.fieldStepConfig.width > 0) {
                            stepCol.width = props.fieldStepConfig.width;
                        }
                        if (props.fieldStepConfig.listFixed) {
                            stepCol.width = stepCol.width || 180;
                            if (props.showCreateTime === undefined || props.showCreateTime) {
                                columns.push(createTimeCol)
                                columnsCount++;
                            }
                            columns.push(stepCol)
                            columnsCount++;
                        } else {
                            columns.push(stepCol)
                            columnsCount++;
                            if (props.showCreateTime === undefined || props.showCreateTime) {
                                columns.push(createTimeCol)
                                columnsCount++;
                            }
                        }
                    } else {
                        if (props.showCreateTime === undefined || props.showCreateTime) {
                            columns.push(createTimeCol)
                            columnsCount++;
                        }
                    }


                    //可prop动态设置宽度
                    newActionW.value = props.actionDefWidth || (32 + 28);
                    if(props.showAction!==false){
                        columns.push({
                            // title:'操作',
                            titleSlotName:'custom-title-action',
                            slotName:'action',
                            width: newActionW,
                            fixed: 'right',
                        })
                        columnsCount++;
                    }


                    //太小出现滚动条

                    let getX = function () {
                        let w=document.body.querySelector('#'+id).clientWidth;
                        if (w > 1640)return undefined;
                        if (columnsCount <= 3)return w > 370 ? undefined : 420;
                        if (columnsCount <= 4)return w > 450 ? undefined : 500;
                        if (columnsCount <= 5)return w > 680 ? undefined : 960;
                        if (columnsCount < 6)return w > 780 ? undefined : 1080;
                        if (columnsCount < 7)return w > 880 ? undefined : 1120;
                        if (columnsCount < 8)return w > 960 ? undefined : 1180;
                        if (columnsCount < 9)return w > 1020 ? undefined : 1240;
                        if (columnsCount < 12)return w > 1460 ? undefined : 1560;
                        return 1640;
                    }


                    columnsVals.value = columns;
                    onresize = () => {
                        scrollX.value = getX();
                        let listSetWidth=0,haveNotSetWidth=false;
                        columnsVals.value.forEach(col => {
                            if(col.width){
                                listSetWidth+=parseInt(col.width);
                            }else{
                                haveNotSetWidth=true;
                            }
                        });
                        if(scrollX.value!==undefined&&!haveNotSetWidth&&scrollX.value>listSetWidth){
                            let boxW=document.body.querySelector('#' + id).clientWidth;
                            if(props.rowSelection){
                                boxW-=84;
                            }
                            scrollX.value=boxW>listSetWidth?undefined:listSetWidth;
                        }
                        if (scrollX.value === undefined) {
                            const tablePath = '#' + id + '>.curd-table table.arco-table-element';
                            if (!document.querySelector('#' + id)
                            || !document.querySelector('#' + id)
                                ||! document.querySelector(tablePath)) {
                                if (!document.querySelector('#' + id + '>.curd-table table') || !document.querySelector('#' + id + '>.curd-table tbody')) {
                                    setTimeout(() => {
                                        onresize();
                                    }, 40)
                                }
                            } else {
                                scrollX.value = document.querySelector('#' + id).clientWidth;
                            }
                        }


                        columnsVals.value.forEach(col => {
                            if (typeof col.fixed !== 'undefined') {
                                if (scrollX.value === undefined) {
                                    if (typeof col.fixedOld === 'undefined') {
                                        col.fixedOld = col.fixed;
                                    }
                                    col.fixed = false;
                                } else if (typeof col.fixedOld !== 'undefined') {
                                    col.fixed = col.fixedOld;
                                }
                            }
                        })

                        if (props.setScrollY) {
                            scrollY.value='100%'
                        }
                    };
                    Vue.nextTick(function () {
                        onresize();
                        window.onresize = (e) => {
                            oldResize(e);
                            onresize();
                        };
                    })


                    let co = {};
                    if (props.childs) {
                        props.childs.forEach(v => {
                            co[v.name] = v;
                        })
                    }


                    titleItems.value=ti;
                    listFieldComponents.value=lfc;
                    fieldObjs.value=fo;
                    childsObjs.value=co;
                });






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
                    expandedRowKeys: Vue.ref([]),
                    guid,
                }
            },
            watch: {
                listColumns(){
                    this.$nextTick(()=>{
                        this.getActionWidthByProps()
                    })
                },
                actionWidth(val) {
                    this.getActionWidthByProps()
                },
                data(data) {
                    Vue.nextTick(() => {
                        this.onresize();
                        setTimeout(() => {
                            this.onresize();
                        }, 40)
                    });
                    this.getActionWidthByProps();
                    const expandedRowKeys = [];
                    if (this.expandAllRows) {
                        const setPids = list => {
                            list.forEach(v => {
                                if (v[this.childrenColumnName] && v[this.childrenColumnName].length > 0) {
                                    expandedRowKeys.push(v.id);
                                    setPids(v[this.childrenColumnName])
                                }
                            })
                        }
                        setPids(data);
                    }
                    this.expandedRowKeys = expandedRowKeys;
                },
            },
            computed:{
                tableSelectedKeys:{
                    get(){
                        return this.selectedKeys
                    },
                    set(val){
                        this.$emit('update:selectedKeys', val)
                    }
                }
            },
            methods: {
                getActionWidthByProps() {
                    let btnWidth = 28;
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
                        this.getChildBtnsByRow(record).forEach(vv=>{
                            childW += this.getTextWidthByBtn(vv.text);
                        })

                        let childAddW = 0;
                        if (this.isCanAddChildren(record)) {
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
                            delW = 14;
                        }
                        let defW=0;
                        if (typeof this.actionWidth === 'function') {
                            defW = this.actionWidth(record);
                        } else if (this.actionWidth) {
                            defW = this.actionWidth;
                        }

                        const btnW = defW+stepWidth + childW + childAddW + showW + editW + delW + this.getBeforeBtnsW(record) + this.getAfterBtnsW(record);//要删掉一个间隔
                        if (btnW > btnWidth) {
                            btnWidth = btnW;
                        }
                    })


                    this.actionW = 32 + btnWidth;
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
                    return 18 + (text.split('').length * 14);
                },
                addChildrenBtnText(row) {
                    return row.childAddBtn && row.childAddBtn.btnTitle ? row.childAddBtn.btnTitle : '添加下级';
                },
                addChildrenBtnColor(row) {
                    if (!row.childAddBtn) {
                        return null;
                    }
                    if (!row.childAddBtn.btnColor) {
                        return null;
                    }
                    return row.childAddBtn.btnColor;
                },
                showBtnText(row) {
                    return row.showBtn && row.showBtn.btnTitle ? row.showBtn.btnTitle : '详情';
                },
                showBtnColor(row) {
                    if (!row.showBtn) {
                        return null;
                    }
                    if (!row.showBtn.btnColor) {
                        return null;
                    }
                    return row.showBtn.btnColor;
                },
                editBtnText(row) {
                    let editText = row.editBtn && row.editBtn.btnTitle ? row.editBtn.btnTitle : '修改';
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
                    if (!row.editBtn) {
                        return null;
                    }
                    if (!row.editBtn.btnColor) {
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
                isCanAddChildren(row) {
                    return this.isTreeIndex && this.canAdd&&row.can_add_children!==false;
                },
                getBeforeBtns(row) {
                    return row.otherBtns ? row.otherBtns.before : [];
                },
                getAfterBtns(row) {
                    return row.otherBtns ? row.otherBtns.after : [];
                },
                getBeforeBtnsW(row) {
                    let w = 0;
                    const btns = this.getBeforeBtns(row);
                    for (let i in btns) {
                        if (btns[i].btnTitle) w += this.getTextWidthByBtn(btns[i].btnTitle)
                    }
                    return w;
                },
                getAfterBtnsW(row) {
                    let w = 0;
                    const btns = this.getAfterBtns(row);
                    for (let i in btns) {
                        if (btns[i].btnTitle) w += this.getTextWidthByBtn(btns[i].btnTitle)
                    }
                    return w;
                },
                refreshId(id) {
                    this.$emit('refreshId', id)
                },
                refreshTable() {
                    this.$emit('refreshTable')
                },
                '$post': vueDefMethods.$post,
                openBox: window.openBox,
                openOtherBtn: window.vueDefMethods.openOtherBtn,
                colStyle(field,row){
                    return Object.assign(Array.isArray(field.listColStyle)?{}:JSON.parse(JSON.stringify(field.listColStyle)),row.__style&&row.__style[field.name]&&typeof row.__style[field.name]==='object'?row.__style[field.name]:{})
                },
                keyValueStr(obj){
                    const arr=[];
                    for(let i in obj){
                        if(typeof obj[i]==='object'){
                            arr.push(this.keyValueStr(obj[i]))
                        }else if(typeof obj[i]==='string'||typeof obj[i]==='number'||(obj[i]&&typeof obj[i].toString!=='undefined')){
                            arr.push(obj[i].toString());
                        }else{
                            arr.push('');
                        }
                    }
                    return arr.join('|');
                },
                getChildBtnsByRow(row){
                    if(!row.childBtns){
                        return [];
                    }
                    const arr=[];
                    for(let i in row.childBtns){
                        if(row.childBtns[i].show){
                            arr.push({
                                key:i,
                                ...row.childBtns[i]
                            })
                        }
                    }
                    return arr;
                },
                log(...data){
                    console.log(...data)
                },
            },
            template: `<div :id="id">
                        <a-table
                            row-key="id"
                            :columns="columns"
                            :data="data"
                            :pagination="pagination&&(!isTreeIndex)&&pagination.pageSize?pagination:false"
                            :loading="loading"
                            class="curd-table"
                            :bordered="isGroup?{headerCell:true,bodyCell:false,wrapper:false}:false"
                            :scroll="{ x: scrollX ,y:scrollY}"
                            :row-selection="rowSelection"
                            :children-column-name="childrenColumnName"
                            :indent-size="indentSize"
                            v-model:expanded-keys="expandedRowKeys"
                            v-model:selected-keys="tableSelectedKeys"
                            column-resizable
                            @change="(...$parameter)=>$emit('change', ...$parameter)"
                            @page-change="(...$parameter)=>$emit('pageChange', ...$parameter)" 
                            @page-size-change="(...$parameter)=>$emit('pageSizeChange', ...$parameter)" 
                            @sorter-change="(...$parameter)=>$emit('sorterChange', ...$parameter)"
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
                                        :style="colStyle(item,record.record)"
                                        v-model:list="data"
                                        @refresh-table="refreshTable"
                                        @refresh-id="refreshId"
                                    ></component>     
                                </slot>
                             </template>
               
                             
                             
                             <template #default-value="record">
                                <slot :name="'f-'+record.column.dataIndex"
                                    :field="fieldObjs[record.column.dataIndex]" 
                                    :record="record">
                                        <span :style="colStyle(fieldObjs[record.column.dataIndex],record.record)">{{record.record[record.column.dataIndex]}}</span>
                                </slot>
                             </template>
                             
                             <template #custom-title-step-info><slot name="custom-title-step-info" :columns="columns">当前步骤</slot></template>
                             <template #step-info="{ record: {stepInfo} }">
                                    <slot name="step-info">
                                        <div class="curd-table-row-step-div">
                                            <div class="curd-table-row-step-title">
                                                <a-tooltip v-if="stepInfo" position="lt">
                                                    <template #content>{{ stepInfo.title }}</template>
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
                             <template #create-time="{ record: {create_time} }">
                                    <slot name="f-create_time">
                                       {{ create_time }}
                                    </slot>
                             </template>
                             
                             
                              
                             <template #custom-title-action><slot name="custom-title-action" :columns="columns">操作</slot></template>
                             <template #action="{ record }">
                                    <a-space class="curd-table-action-btns">
                                        <template #split>
                                          <a-divider direction="vertical" class="curd-table-action-divider" />
                                        </template>
                                        
                                        <slot name="do-before" :record="record"></slot>
                                        
                                        <a v-for="btn in getBeforeBtns(record)" :key="keyValueStr(btn)" @click="openOtherBtn(btn,record)" :style="{color: btn.btnColor}">{{btn.btnTitle}}</a>
                                        
                                        <slot name="do" :record="record">
                                            <a v-if="isCanShowInfo(record)" @click="openShow(record)" :style="{color: showBtnColor(record)}">{{showBtnText(record)}}</a>
                                            <template v-if="isCanEdit(record)">
                                                <a @click="openEdit(record)" :style="{color: editBtnColor(record)}">{{editBtnText(record)}}</a>
                                            </template>
                                        </slot>
                                        
                                        
                                        <template v-if="stepBtnShow(record)">
                                            <slot name="step-next-btn" :record="record">
                                                <a v-if="record.nextStepInfo.config.listBtnText" @click="openNext(record)" :style="{color:record.nextStepInfo.config.listBtnColor}" :class="record.nextStepInfo.config.listBtnClass" class="open-step-a-class">{{record.nextStepInfo.config.listBtnText}}</a>
                                            </slot>
                                        </template>
                                        
                                        
                                        <slot name="child-btns" :record="record">
                                            <a v-for="vo in getChildBtnsByRow(record)" :key="keyValueStr(vo)" @click="openChildList(record,childsObjs[vo.key],vo)" :style="{color: vo.color}" class="open-child-a-class">{{vo.text}}</a>
                                        </slot>
                                        
                                        
                                        <a v-if="isCanAddChildren(record)" @click="openAddChildren(record)" :style="{color: addChildrenBtnColor(record)}">{{addChildrenBtnText(record)}}</a>
                                        <a  v-for="btn in getAfterBtns(record)" :key="keyValueStr(btn)" @click="openOtherBtn(btn,record)" :style="{color: btn.btnColor}">{{btn.btnTitle}}</a>
                                        
                                        <slot name="do-after" :record="record"></slot>
                                        
                                        
                                        <a-popconfirm
                                            type="warning"
                                            v-if="isCanDel(record)"
                                            position="left"
                                            content="您确定要删除这条数据吗？"
                                            @ok="onDelete(record)"
                                        >
                                            <icon-delete class="pub-remove-icon" />
                                        </a-popconfirm>
                                    </a-space>
                            </template>
                                
                            <template v-if="$slots.footer" #footer><slot name="footer" :columns="columns" :current-page-data="data"></slot></template>
                        </a-table>
                    </div>`,
        })


        /*** 筛选组件 ***/
        app.component('CurdFilter', {
            props: ['filterConfig', 'name', 'class', 'title', 'childs', 'filterValues', 'loading'],
            setup(props, ctx) {
                const filterSource = Vue.ref({});
                const modelTitles=Vue.ref({});
                Vue.watchEffect(()=>{
                    const fs = {filterConfig:props.filterConfig.map(function (v) {
                            if (v.group) {
                                v.title = v.group + ' >' + v.title
                            }
                            return v;
                        })}
                    const mt = {
                        [props.class]: props.title,
                        [props.name]: props.title,
                    };
                    let childFList=props.childs.filter(v=>v.filterConfig&&v.filterConfig.length>0)
                    for (let i in childFList) {
                        fs[childFList[i].name] = childFList[i].filterConfig.map(function (v) {
                            if (v.group) {
                                v.title = v.group + ' >' + v.title
                            }
                            return v;
                        })

                        mt[childFList[i].class] = childFList[i].title;
                        mt[childFList[i].name] = childFList[i].title;
                    }
                    filterSource.value=fs;
                    modelTitles.value=mt;
                })

                return {
                    filterSource,
                    modelTitles,
                    filterData: Vue.ref({}),
                    childFilterData: Vue.ref({}),
                    showMoreFilter: Vue.ref(false),
                    oldFilterConfig:Vue.ref({}),
                }
            },
            computed: {
                base() {
                    return {
                        name: 'filterConfig',
                        filterConfig: this.filterSource.filterConfig,
                        filterData: this.filterData
                    }
                },
                childFilterEmptys(){
                    if(!this.childs){
                        return [];
                    }
                    return this.childs.filter(v=>v.filterConfig&&v.filterConfig.length>0);
                },
                haveFielter(){
                    return this.filterSource.filterConfig.some(item => this.filterGroupBaseItemIsShow(item)) ||
                        this.childFilterEmptys.some(child => Object.values(this.filterSource[child.name]).some(item => this.filterGroupItemIsShow(item,child)));
                },
                hideFilterList(){
                    const returns=[];
                    for(const key in this.filterSource){
                        const vo=this.filterSource[key];
                        const items=this.moreShowItems(vo);
                        if(items.length===0){
                            continue;
                        }
                        returns.push({
                            key:key,
                            modelTitle:this.modelTitles[key]||'',
                            items:items,
                        })
                    }
                    return returns;
                },
                filterMenuBoxClass(){
                    const count = this.hideFilterList.reduce((count, vo) => count + vo.items.length, 0);
                    const GRID_CLASSES = {
                        1: ['no-grid'],
                        2: ['grid-column-2'],
                        3: ['grid-column-3'],
                        4: ['grid-column-4']
                    };
                    return GRID_CLASSES[Math.min(Math.floor(window.innerWidth/360), Math.ceil(count / 16),4)];
                }
            },
            watch:{
                haveFielter:{
                    immediate: true,
                    handler(newVal) {
                        this.$emit('haveFielterShowChange',newVal);
                    },
                },
            },
            created(){

                this.oldFilterConfig={
                    filterSource:JSON.parse(JSON.stringify(this.filterSource)),
                    filterData:JSON.parse(JSON.stringify(this.filterData)),
                    childFilterData:JSON.parse(JSON.stringify(this.childFilterData)),
                    showMoreFilter:!!this.showMoreFilter,
                }
            },
            methods: {
                restFilter(){
                    const filterSource=JSON.parse(JSON.stringify(this.oldFilterConfig.filterSource));
                    for(let i in this.filterSource){
                        filterSource[i].forEach(v=>{
                            v.rest=true;
                        })
                    }
                    this.filterSource=filterSource;
                    this.filterData=JSON.parse(JSON.stringify(this.oldFilterConfig.filterData));
                    this.childFilterData=JSON.parse(JSON.stringify(this.oldFilterConfig.childFilterData));
                    this.showMoreFilter=!!this.oldFilterConfig.showMoreFilter;
                    setTimeout(()=>{
                        for(let i in this.filterSource){
                            this.filterSource[i].forEach(v=>{
                                v.rest=false;
                            })
                        }
                    })
                },
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
                filterGroupBaseItemIsShow(item){
                    return item.show&&(!this.filterValues||!this.filterValues[item.name]);
                },
                search(val, item) {
                    item.activeValue = val;
                    const data = this.getFilterData();
                    if (this.$refs) {
                        for (let key in this.$refs) {
                            if (key.indexOf('filters.') === 0 && typeof this.$refs[key].onParentSearch === 'function') {
                                this.$refs[key].onParentSearch();
                            }
                        }
                    }
                    this.$emit('search', data);
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
                },
                createWidthEl(box,widthElId){
                    let widthEl=box.document.getElementById(widthElId);
                    if(!widthEl){
                        widthEl=box.document.createElement('span');
                        widthEl.style='font-size:14px;position:fixed;z-index:-1;opacity:0;'
                        widthEl.id=widthElId;
                        box.document.body.appendChild(widthEl);
                    }
                    return widthEl;
                },
                getItems(items,group,child){
                    const w={l:0,r:0};
                    const newItems=child?items.filter(item=>this.filterGroupItemIsShow(item, child)):items.filter(item=>this.filterGroupBaseItemIsShow(item));
                    const widthElId='filter-lable-width-el';

                    let widthEl=this.createWidthEl(window,widthElId);
                    widthEl.innerText='check-width';
                    if(widthEl.clientWidth===0){
                        widthEl=this.createWidthEl(top,widthElId);
                    }

                    newItems.forEach((item,index)=>{
                        widthEl.innerText=item.title.toString();
                        const width=widthEl.clientWidth;
                        const n=index%2>0?'r':'l';
                        if(w[n]<width){
                            w[n]=width;
                        }
                    })
                    widthEl.innerText='';
                    return newItems.map((item,index)=>{
                        const n=index%2>0?'r':'l';
                        item.labelWidth=w[n];
                        return item;
                    });
                }
            },
            template: `<div class="curd-filter-box" :class="{'empty-filter-items':!haveFielter}">
                        <a-spin :loading="loading">
                            <div class="filter-box-title" v-if="childFilterEmptys.length>0&&filterGroupIsShow(base)">{{title}}：</div>
                            <div class="filter-box-div" v-if="filterGroupIsShow(base)">
                                <transition-group name="bounce">
                                    <template v-for="(item,index) in getItems(filterSource.filterConfig,'filterConfig')">
                                        <div class="filter-item-box" :key="item.name" :style="item.gridCol">
                                            <div class="filter-item">
                                                <div class="filter-item-l" :style="{width: item.labelWidth+'px'}">{{item.title}}</div> 
                                                <div class="filter-item-r">
                                                 <component v-if="item.rest!==true"
                                                            :is="item.type" 
                                                            :config="item"
                                                            :ref="'filters.filterConfig.'+item.name"
                                                            @search="search($event,item)"
                                                    ></component>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </transition-group>
                            </div>
                            <template v-if="childFilterEmptys.length>0">
                                <template v-for="child in childFilterEmptys">
                                    <div class="filter-box-title" v-show="filterGroupIsShow(child)">{{child.title}}：</div>
                                    <div class="filter-box-div" v-show="filterGroupIsShow(child)">
                                        <transition-group name="bounce">
                                            <template v-for="(item,index) in getItems(filterSource[child.name],child.name,child)" :key="item.name">
                                                <div class="filter-item-box" :style="item.gridCol">
                                                    <div class="filter-item">
                                                        <div class="filter-item-l" :style="{width:item.labelWidth+'px'}">{{item.title}}</div> 
                                                        <div class="filter-item-r">
                                                            <component v-if="item.rest!==true"
                                                                    :is="item.type" 
                                                                    :config="item"
                                                                    :ref="'filters.'+child.name+'.'+item.name"
                                                                    @search="search($event,item)"
                                                            ></component>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </transition-group>
                                    </div>
                                </template>
                            </template>
                        </a-spin>
                        <div class="filter-box-bottom-do"></div>
                        <div class="filter-sub-btn-box">
                            <a-divider v-if="showMoreFilter">
                                <a-dropdown trigger="click" class="filter-box-dropdown">
                                    <a class="arco-dropdown-link" style="font-size: 14px" >
                                        <template v-if="haveFielter">更多筛选 <icon-down/></template>
                                        <icon-search v-else />
                                    </a>
                                    <template #content>
                                        <div class="filter-menu-box" :class="filterMenuBoxClass">
                                            <template v-for="vo in hideFilterList">
                                                <div v-if="vo.modelTitle" class="filter-select-show-item-title">{{vo.modelTitle}}</div>
                                                <div class="filter-select-show-item-box">
                                                    <a-doption v-for="item in vo.items">
                                                        <a href="javascript:;"
                                                           class="filter-select-show-item"
                                                           :class="{checked:item.show}"
                                                           @click="item.show=!item.show">
                                                            <div class="filter-select-show-title">{{ item.title }}</div>
                                                           <icon-check class="anticon"></icon-check>
                                                        </a>
                                                    </a-doption>
                                                </div>
                                            </template>
                                        </div>
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
        if(window.vueComponents){
            for(let componentName in window.vueComponents){
                app.component(componentName, window.vueComponents[componentName]);
            }
        }
        app.mount('#app')

    };
});