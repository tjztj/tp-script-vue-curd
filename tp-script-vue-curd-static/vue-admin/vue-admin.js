/**
 * tj 1079798840@qq.com
 */
define(['axios','qs'], function ( axios,Qs) {
    /****
     * 自定义Promise
     * @param func
     * @param functions
     * @returns {f}
     * @constructor
     */
    function MyPromise(doFunction){const f=function(fn){this.fn=fn;return this};f.prototype._functions={};f.prototype.on=function(event,func){this._functions[event]=func;return this};f.prototype.end=function(){this.fn((event,...params)=>{if(this._functions[event])this._functions[event](...params);return this})};return new f(doFunction)}



    let styles = document.querySelectorAll('#app style');
    if (styles) for (let i = 0; i < styles.length; i++) document.head.appendChild(styles[i]);




    /**
     * 初始化请求
     */
    const service=axios.create({baseURL:'/'+window.VUE_CURD.MODULE+'/',withCredentials:true,timeout:150000});service.interceptors.response.use(response=>{const res=response.data; if(res.code==1){return res}antd.message.error(res.msg||'失败');if(res.url&&res.url.indexOf(vueData.loginUrl)!==-1){antd.Modal.confirm({content:'登录或已过期，可以取消以留在此页，或重新登录',okText:'确认退出',cancelText:'取消',onOk(){location.href=res.url}})}return Promise.reject(res)},error=>{if(typeof error==='string'){error={code:0,msg:error,data:[],}}else if(!error.msg){console.error(error);error={code:0,msg:'发生错误',data:[],}}antd.message.error(error.msg,6);return Promise.reject(error)})


    /****窗口方法***/
    window.parseTime=function(time,cFormat){if(arguments.length===0){return null}const format=cFormat||'{y}-{m}-{d} {h}:{i}:{s}';let date; if(typeof time==='object'){date=time}else{if((typeof time==='string')&&(/^-?[0-9]+$/.test(time))){time=parseInt(time)}if((typeof time==='number')&&(time.toString().length===10||time.toString().length===9)){time=time*1000}date=new Date(time)}const formatObj={y:date.getFullYear(),m:date.getMonth()+1,d:date.getDate(),h:date.getHours(),i:date.getMinutes(),s:date.getSeconds(),a:date.getDay()};return format.replace(/{(y|m|d|h|i|s|a)+}/g,(result,key)=>{let value=formatObj[key];if(key==='a'){return['日','一','二','三','四','五','六'][value]}if(result.length>0&&value<10){value='0'+value}return value||0})};
    window.getMonthWeek=function(dateStr){let date=new Date(dateStr);let dateStart=new Date((new Date(dateStr).setDate(1)));let firstWeek=1;if(dateStart.getDay()===1){firstWeek=1}else if(dateStart.getDay()===0){firstWeek=8-7+1}else{firstWeek=8-dateStart.getDay()+1}let weekIndex=1;let c=date.getDate();if(date.getDay()===1&&date.getDate()<7){weekIndex=1}else if(c<firstWeek){weekIndex=-1}else{if(c<7){weekIndex=Math.ceil(c/7)}else{c=c-firstWeek+1;if(c%7===0){if(dateStart.getDay()!==6){weekIndex=c/7}else{weekIndex=c/7+1}}else{weekIndex=Math.ceil(c/7)}}}let month=date.getMonth();let year=date.getFullYear();if(weekIndex<0){if(month==0){month=11;year--}else{month--}let new_date=new Date(year,parseInt(month)+1,0);return getMonthWeek(new_date.getFullYear()+'-'+(parseInt(new_date.getMonth())+1)+'-'+new_date.getDate())}month++;return year+'年'+(month>9?month:('0'+month))+'月第'+weekIndex+'周'};
    // weekIndexInYear('2020-01-01')//2019年第52周
    window.weekIndexInYear=function(dateStr){let nowDate=new Date(dateStr);let initTime=new Date(dateStr);initTime.setMonth(0);initTime.setDate(1);let differenceVal=nowDate-initTime;let todayYear=Math.ceil(differenceVal/(24*60*60*1000));let index=Math.ceil(todayYear/7);if(index==0){return weekIndexInYear((nowDate.getFullYear()-1)+'-12-31')}return nowDate.getFullYear()+'年第'+index+'周'};
    //获取一周的日期范围
    window.getLastWeek=function(dateStr){let date=new Date(dateStr);let today=date.getDay();let stepSunDay=-today+1;if(today==0){stepSunDay=-7}let stepMonday=7-today;let time=date.getTime();let monday=new Date(time+stepSunDay*24*3600*1000);let sunday=new Date(time+stepMonday*24*3600*1000);return[monday.getFullYear()+'-'+(monday.getMonth()+1)+'-'+monday.getDate(),sunday.getFullYear()+'-'+(sunday.getMonth()+1)+'-'+sunday.getDate(),]}
    //生成随机 GUID 数
    window.guid=function(){function S4(){return(((1+Math.random())*65536)|0).toString(16).substring(1)}return(S4()+S4()+"-"+S4()+"-"+S4()+"-"+S4()+"-"+S4()+S4()+S4())};

    let openBox=function(option){
        const box = top;
        let vueObj=this;
        return MyPromise(function (trigger) {
            option = Object.assign({
                title: '',
                type: 2,
                area: ['60vw', '100vh'],
                content: '',
                maxmin: false,
                moveOut: false,
                anim: 2,
                offset: 'rt',
                success(layero, index) {
                    var body = box.layui.layer.getChildFrame('body', index);
                    layero.css('overflow','hidden');
                    layero.find('iframe')[0].contentWindow.listVue=vueObj;//将当前页面的this保存到新页面的window里面
                    layero.find('iframe')[0].contentWindow.parentWindow=window;
                    layero.close=function(){
                        box.layui.layer.close(index);
                    }
                    if (body.length > 0) {
                        body.on('closeIframe', function () {
                            layero.close();
                        })
                        layero.find('iframe').css('padding','0px 0 28px 0')
                        box.$.each(body, function (i, v) {
                            // todo 优化弹出层背景色修改
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
                if(option.content.indexOf('is_iframe_goto') == -1){
                    option.content += (option.content.indexOf('?') == -1 ? '?' : '&') + 'is_iframe_goto=1';
                }
                if(option.content.indexOf('is_vue_open') == -1){
                    option.content += (option.content.indexOf('?') == -1 ? '?' : '&') + 'is_vue_open=1';
                }
            }
            box.layer.open(option);
        })
    };

    /**
     * 一些自定义的处理函数
     */
    const methods = {
        parseTime: window.parseTime,
        back() {
            window.history.back()
        },
        openBox,
        '$get'(url, params){
            if(url.indexOf('/'+window.VUE_CURD.MODULE+'/')===0){url=url.replace('\/'+window.VUE_CURD.MODULE+'\/','')}
            return service({url, method: 'get',params,headers:{'X-REQUESTED-WITH':'xmlhttprequest'}})
        },
        '$post'(url, data){
            if(url.indexOf('/'+window.VUE_CURD.MODULE+'/')===0){url=url.replace('\/'+window.VUE_CURD.MODULE+'\/','')}
            return service({url, method: 'post',data:Qs.stringify(data),headers:{'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8','X-REQUESTED-WITH':'xmlhttprequest'}})
        },
        '$request'(){
            return service;
        },
        pageIsInit(){
            if (document.getElementById('app')) document.getElementById('app').style.display = 'block'
            if (document.getElementById('app-loading')) document.getElementById('app-loading').style.display = 'none'
        },
        showImages(imgs, start){
            window.top.showImages(imgs, start);
        },
        zhCn(){const Pagination={items_per_page:'条/页',jump_to:'跳至',jump_to_confirm:'确定',page:'页',prev_page:'上一页',next_page:'下一页',prev_5:'向前 5 页',next_5:'向后 5 页',prev_3:'向前 3 页',next_3:'向后 3 页',};const DatePicker={lang:{placeholder:'请选择日期',rangePlaceholder:['开始日期','结束日期'],today:'今天',now:'此刻',backToToday:'返回今天',ok:'确定',timeSelect:'选择时间',dateSelect:'选择日期',weekSelect:'选择周',clear:'清除',month:'月',year:'年',previousMonth:'上个月 (翻页上键)',nextMonth:'下个月 (翻页下键)',monthSelect:'选择月份',yearSelect:'选择年份',decadeSelect:'选择年代',yearFormat:'YYYY年',dayFormat:'D日',dateFormat:'YYYY年M月D日',dateTimeFormat:'YYYY年M月D日 HH时mm分ss秒',previousYear:'上一年 (Control键加左方向键)',nextYear:'下一年 (Control键加右方向键)',previousDecade:'上一年代',nextDecade:'下一年代',previousCentury:'上一世纪',nextCentury:'下一世纪',},timePickerLocale:{placeholder:'请选择时间',},};return{locale:'zh-cn',Pagination,DatePicker,TimePicker:{placeholder:'请选择时间',},Calendar:DatePicker,ColorPicker:{'btn:save':'保存','btn:cancel':'取消','btn:clear':'清除',},global:{placeholder:'请选择',},Table:{filterTitle:'筛选',filterConfirm:'确定',filterReset:'重置',selectAll:'全选当页',selectInvert:'反选当页',sortTitle:'排序',expand:'展开行',collapse:'关闭行',},Modal:{okText:'确定',cancelText:'取消',justOkText:'知道了',},Popconfirm:{cancelText:'取消',okText:'确定',},Transfer:{searchPlaceholder:'请输入搜索内容',itemUnit:'项',itemsUnit:'项',},Upload:{uploading:'文件上传中',removeFile:'删除文件',uploadError:'上传错误',previewFile:'预览文件',downloadFile:'下载文件',},Empty:{description:'暂无数据',},Icon:{icon:'图标',},Text:{edit:'编辑',copy:'复制',copied:'复制成功',expand:'展开',},PageHeader:{back:'返回',},}},
        log(obj){
            return console.log(obj);
        }
    };


    return function (option) {
        option.data=option.data||function(){
            return {};
        };
        if(!option.mounted) {
            option.mounted = function () {
                this.pageIsInit();
            }
        }
        option.methods = Object.assign(methods, option.methods || {});
        window.app = Vue.createApp(option)
        app.use(antd)
        app.component('PlusOutlined',{
            template:`<span role="img" aria-label="plus" class="anticon anticon-plus"><svg class="" data-icon="plus" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><defs><style></style></defs><path d="M482 152h60q8 0 8 8v704q0 8-8 8h-60q-8 0-8-8V160q0-8 8-8z"></path><path d="M176 474h672q8 0 8 8v60q0 8-8 8H176q-8 0-8-8v-60q0-8 8-8z"></path></svg></span>`
        })
        app.component('ReloadOutlined',{
            template:`<span role="img" aria-label="reload" class="anticon anticon-reload"><svg class="" data-icon="reload" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M909.1 209.3l-56.4 44.1C775.8 155.1 656.2 92 521.9 92 290 92 102.3 279.5 102 511.5 101.7 743.7 289.8 932 521.9 932c181.3 0 335.8-115 394.6-276.1 1.5-4.2-.7-8.9-4.9-10.3l-56.7-19.5a8 8 0 00-10.1 4.8c-1.8 5-3.8 10-5.9 14.9-17.3 41-42.1 77.8-73.7 109.4A344.77 344.77 0 01655.9 829c-42.3 17.9-87.4 27-133.8 27-46.5 0-91.5-9.1-133.8-27A341.5 341.5 0 01279 755.2a342.16 342.16 0 01-73.7-109.4c-17.9-42.4-27-87.4-27-133.9s9.1-91.5 27-133.9c17.3-41 42.1-77.8 73.7-109.4 31.6-31.6 68.4-56.4 109.3-73.8 42.3-17.9 87.4-27 133.8-27 46.5 0 91.5 9.1 133.8 27a341.5 341.5 0 01109.3 73.8c9.9 9.9 19.2 20.4 27.8 31.4l-60.2 47a8 8 0 003 14.1l175.6 43c5 1.2 9.9-2.6 9.9-7.7l.8-180.9c-.1-6.6-7.8-10.3-13-6.2z"></path></svg></span>`
        })
        app.component('CheckOutlined',{
            template:`<span role="img" aria-label="check" class="anticon anticon-check"><svg class="" data-icon="check" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M912 190h-69.9c-9.8 0-19.1 4.5-25.1 12.2L404.7 724.5 207 474a32 32 0 00-25.1-12.2H112c-6.7 0-10.4 7.7-6.3 12.9l273.9 347c12.8 16.2 37.4 16.2 50.3 0l488.4-618.9c4.1-5.1.4-12.8-6.3-12.8z"></path></svg></span>`
        })
        app.component('PictureOutlined',{
            template:`<span role="img" aria-label="picture" class="anticon anticon-picture"><svg class="" data-icon="picture" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M928 160H96c-17.7 0-32 14.3-32 32v640c0 17.7 14.3 32 32 32h832c17.7 0 32-14.3 32-32V192c0-17.7-14.3-32-32-32zm-40 632H136v-39.9l138.5-164.3 150.1 178L658.1 489 888 761.6V792zm0-129.8L664.2 396.8c-3.2-3.8-9-3.8-12.2 0L424.6 666.4l-144-170.7c-3.2-3.8-9-3.8-12.2 0L136 652.7V232h752v430.2zM304 456a88 88 0 100-176 88 88 0 000 176zm0-116c15.5 0 28 12.5 28 28s-12.5 28-28 28-28-12.5-28-28 12.5-28 28-28z"></path></svg></span>`
        })
        app.component('FileImageOutlined',{
            template:`<span role="img" aria-label="file-image" class="anticon anticon-file-image"><svg class="" data-icon="file-image" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M553.1 509.1l-77.8 99.2-41.1-52.4a8 8 0 00-12.6 0l-99.8 127.2a7.98 7.98 0 006.3 12.9H696c6.7 0 10.4-7.7 6.3-12.9l-136.5-174a8.1 8.1 0 00-12.7 0zM360 442a40 40 0 1080 0 40 40 0 10-80 0zm494.6-153.4L639.4 73.4c-6-6-14.1-9.4-22.6-9.4H192c-17.7 0-32 14.3-32 32v832c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V311.3c0-8.5-3.4-16.7-9.4-22.7zM790.2 326H602V137.8L790.2 326zm1.8 562H232V136h302v216a42 42 0 0042 42h216v494z"></path></svg></span>`
        })
        app.component('FileExcelOutlined',{
            template:`<span role="img" aria-label="file-excel" class="anticon anticon-file-excel"><svg class="" data-icon="file-excel" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M854.6 288.6L639.4 73.4c-6-6-14.1-9.4-22.6-9.4H192c-17.7 0-32 14.3-32 32v832c0 17.7 14.3 32 32 32h640c17.7 0 32-14.3 32-32V311.3c0-8.5-3.4-16.7-9.4-22.7zM790.2 326H602V137.8L790.2 326zm1.8 562H232V136h302v216a42 42 0 0042 42h216v494zM514.1 580.1l-61.8-102.4c-2.2-3.6-6.1-5.8-10.3-5.8h-38.4c-2.3 0-4.5.6-6.4 1.9-5.6 3.5-7.3 10.9-3.7 16.6l82.3 130.4-83.4 132.8a12.04 12.04 0 0010.2 18.4h34.5c4.2 0 8-2.2 10.2-5.7L510 664.8l62.3 101.4c2.2 3.6 6.1 5.7 10.2 5.7H620c2.3 0 4.5-.7 6.5-1.9 5.6-3.6 7.2-11 3.6-16.6l-84-130.4 85.3-132.5a12.04 12.04 0 00-10.1-18.5h-35.7c-4.2 0-8.1 2.2-10.3 5.8l-61.2 102.3z"></path></svg></span>`
        })
        app.component('DownloadOutlined',{
            template:`<span role="img" aria-label="download" class="anticon anticon-download"><svg class="" data-icon="download" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M505.7 661a8 8 0 0012.6 0l112-141.7c4.1-5.2.4-12.9-6.3-12.9h-74.1V168c0-4.4-3.6-8-8-8h-60c-4.4 0-8 3.6-8 8v338.3H400c-6.7 0-10.4 7.7-6.3 12.9l112 141.8zM878 626h-60c-4.4 0-8 3.6-8 8v154H214V634c0-4.4-3.6-8-8-8h-60c-4.4 0-8 3.6-8 8v198c0 17.7 14.3 32 32 32h684c17.7 0 32-14.3 32-32V634c0-4.4-3.6-8-8-8z"></path></svg></span>`
        })
        app.component('DownOutlined',{
            template:`<span role="img" aria-label="down" class="anticon anticon-down"><svg class="" data-icon="down" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M884 256h-75c-5.1 0-9.9 2.5-12.9 6.6L512 654.2 227.9 262.6c-3-4.1-7.8-6.6-12.9-6.6h-75c-6.5 0-10.3 7.4-6.5 12.7l352.6 486.1c12.8 17.6 39 17.6 51.7 0l352.6-486.1c3.9-5.3.1-12.7-6.4-12.7z"></path></svg></span>`
        })
        app.component('SettingOutlined',{
            template:`<span role="img" aria-label="setting" class="anticon anticon-setting"><svg class="" data-icon="setting" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M924.8 625.7l-65.5-56c3.1-19 4.7-38.4 4.7-57.8s-1.6-38.8-4.7-57.8l65.5-56a32.03 32.03 0 009.3-35.2l-.9-2.6a443.74 443.74 0 00-79.7-137.9l-1.8-2.1a32.12 32.12 0 00-35.1-9.5l-81.3 28.9c-30-24.6-63.5-44-99.7-57.6l-15.7-85a32.05 32.05 0 00-25.8-25.7l-2.7-.5c-52.1-9.4-106.9-9.4-159 0l-2.7.5a32.05 32.05 0 00-25.8 25.7l-15.8 85.4a351.86 351.86 0 00-99 57.4l-81.9-29.1a32 32 0 00-35.1 9.5l-1.8 2.1a446.02 446.02 0 00-79.7 137.9l-.9 2.6c-4.5 12.5-.8 26.5 9.3 35.2l66.3 56.6c-3.1 18.8-4.6 38-4.6 57.1 0 19.2 1.5 38.4 4.6 57.1L99 625.5a32.03 32.03 0 00-9.3 35.2l.9 2.6c18.1 50.4 44.9 96.9 79.7 137.9l1.8 2.1a32.12 32.12 0 0035.1 9.5l81.9-29.1c29.8 24.5 63.1 43.9 99 57.4l15.8 85.4a32.05 32.05 0 0025.8 25.7l2.7.5a449.4 449.4 0 00159 0l2.7-.5a32.05 32.05 0 0025.8-25.7l15.7-85a350 350 0 0099.7-57.6l81.3 28.9a32 32 0 0035.1-9.5l1.8-2.1c34.8-41.1 61.6-87.5 79.7-137.9l.9-2.6c4.5-12.3.8-26.3-9.3-35zM788.3 465.9c2.5 15.1 3.8 30.6 3.8 46.1s-1.3 31-3.8 46.1l-6.6 40.1 74.7 63.9a370.03 370.03 0 01-42.6 73.6L721 702.8l-31.4 25.8c-23.9 19.6-50.5 35-79.3 45.8l-38.1 14.3-17.9 97a377.5 377.5 0 01-85 0l-17.9-97.2-37.8-14.5c-28.5-10.8-55-26.2-78.7-45.7l-31.4-25.9-93.4 33.2c-17-22.9-31.2-47.6-42.6-73.6l75.5-64.5-6.5-40c-2.4-14.9-3.7-30.3-3.7-45.5 0-15.3 1.2-30.6 3.7-45.5l6.5-40-75.5-64.5c11.3-26.1 25.6-50.7 42.6-73.6l93.4 33.2 31.4-25.9c23.7-19.5 50.2-34.9 78.7-45.7l37.9-14.3 17.9-97.2c28.1-3.2 56.8-3.2 85 0l17.9 97 38.1 14.3c28.7 10.8 55.4 26.2 79.3 45.8l31.4 25.8 92.8-32.9c17 22.9 31.2 47.6 42.6 73.6L781.8 426l6.5 39.9zM512 326c-97.2 0-176 78.8-176 176s78.8 176 176 176 176-78.8 176-176-78.8-176-176-176zm79.2 255.2A111.6 111.6 0 01512 614c-29.9 0-58-11.7-79.2-32.8A111.6 111.6 0 01400 502c0-29.9 11.7-58 32.8-79.2C454 401.6 482.1 390 512 390c29.9 0 58 11.6 79.2 32.8A111.6 111.6 0 01624 502c0 29.9-11.7 58-32.8 79.2z"></path></svg></span>`
        })
        app.component('SearchOutlined',{
            template:`<span role="img" aria-label="search" class="anticon anticon-search"><svg class="" data-icon="search" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M909.6 854.5L649.9 594.8C690.2 542.7 712 479 712 412c0-80.2-31.3-155.4-87.9-212.1-56.6-56.7-132-87.9-212.1-87.9s-155.5 31.3-212.1 87.9C143.2 256.5 112 331.8 112 412c0 80.1 31.3 155.5 87.9 212.1C256.5 680.8 331.8 712 412 712c67 0 130.6-21.8 182.7-62l259.7 259.6a8.2 8.2 0 0011.6 0l43.6-43.5a8.2 8.2 0 000-11.6zM570.4 570.4C528 612.7 471.8 636 412 636s-116-23.3-158.4-65.6C211.3 528 188 471.8 188 412s23.3-116.1 65.6-158.4C296 211.3 352.2 188 412 188s116.1 23.2 158.4 65.6S636 352.2 636 412s-23.3 116.1-65.6 158.4z"></path></svg></span>`
        })
        app.component('EditOutlined',{
            template:`<span role="img" aria-label="edit" class="anticon anticon-edit"><svg class="" data-icon="edit" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M257.7 752c2 0 4-.2 6-.5L431.9 722c2-.4 3.9-1.3 5.3-2.8l423.9-423.9a9.96 9.96 0 000-14.1L694.9 114.9c-1.9-1.9-4.4-2.9-7.1-2.9s-5.2 1-7.1 2.9L256.8 538.8c-1.5 1.5-2.4 3.3-2.8 5.3l-29.5 168.2a33.5 33.5 0 009.4 29.8c6.6 6.4 14.9 9.9 23.8 9.9zm67.4-174.4L687.8 215l73.3 73.3-362.7 362.6-88.9 15.7 15.6-89zM880 836H144c-17.7 0-32 14.3-32 32v36c0 4.4 3.6 8 8 8h784c4.4 0 8-3.6 8-8v-36c0-17.7-14.3-32-32-32z"></path></svg></span>`
        })
        app.component('CloseOutlined',{
            template:`<span role="img" aria-label="close" class="anticon anticon-close"><svg class="" data-icon="close" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M563.8 512l262.5-312.9c4.4-5.2.7-13.1-6.1-13.1h-79.8c-4.7 0-9.2 2.1-12.3 5.7L511.6 449.8 295.1 191.7c-3-3.6-7.5-5.7-12.3-5.7H203c-6.8 0-10.5 7.9-6.1 13.1L459.4 512 196.9 824.9A7.95 7.95 0 00203 838h79.8c4.7 0 9.2-2.1 12.3-5.7l216.5-258.1 216.5 258.1c3 3.6 7.5 5.7 12.3 5.7h79.8c6.8 0 10.5-7.9 6.1-13.1L563.8 512z"></path></svg></span>`
        })
        app.component('delOutlined',{
            template:`<span role="img" aria-label="close" class="anticon anticon-close"><svg class="" data-icon="close" width="1em" height="1em" fill="currentColor" aria-hidden="true" viewBox="64 64 896 896" focusable="false"><path d="M977.455 279.273H47.11c-20.311 0-46.264-26.517-46.264-46.264-0.564-20.875 25.953-46.827 46.264-46.827H233.29V93.09C232.727 32.723 265.451 0 325.818 0h369.543c59.804 0 95.348 32.723 95.348 93.09v93.092H976.89c20.311 0 46.264 26.517 46.264 46.263 0.564 20.31-25.389 46.828-45.7 46.828zM698.182 139.918c0-20.31-26.517-46.263-46.264-46.263H372.646c-20.311 0-46.264 26.517-46.264 46.263v46.264h372.364l-0.564-46.264zM465.737 372.364v465.454h-93.091V372.364h93.09z m186.181 0v465.454h-93.09V372.364h93.09z m-465.454-46.828c20.31 0 46.827 26.517 46.827 46.263v511.718c0 20.311 26.517 46.264 46.264 46.264h465.454c20.311 0 46.264-26.517 46.264-46.264V372.364c0-20.311 26.517-46.264 46.263-46.264 20.31 0 46.263 26.517 46.263 46.264v558.545c0 60.368-32.722 93.091-93.09 93.091H232.163c-60.368 0-93.09-32.723-93.09-93.09V372.363c0.563-20.311 27.08-46.828 47.39-46.828z m0 0"></path></svg></span>`
        })

        /*** 年选择器 ***/
        app.component('AYearPicker',{data(){return{yearOpen:false,}},props:['value'],methods:{handleOpenChange(status){this.yearOpen=status},handlePanelChange(value){this.$emit('update:value',value.format('YYYY'));this.handleOpenChange(false)},clearYear(){this.$emit('update:value','')},},template:`<a-date-picker v-model:value="value"mode="year"format="YYYY"value-format="YYYY"placeholder="请选择年份"style="width: 100%":open="yearOpen"@open-change="handleOpenChange"@panel-change="handlePanelChange"@change="clearYear"></a-date-picker>`,})


        /*** 周选择器 ***/
        app.component('WeekSelect',{props:['value','placeholder'],setup(props,ctx){let momentVal=null;let isOpen=Vue.ref(false);let format=()=>{if(isOpen.value){return parseTime(props.value,' {y}年')}let week=getLastWeek(props.value);return getMonthWeek(props.value)+'（'+week[0]+' ~ '+week[1]+'）'};if(props.value){if(/^\d+$/g.test(props.value.toString())){momentVal=parseTime(props.value,'{y}-{m}-{d}');props.value=momentVal}else{momentVal=props.value}momentVal=moment(momentVal);momentVal.format=format}return{momentVal:Vue.ref(momentVal),isOpen:isOpen,format}},watch:{value(val){if(!val){this.momentVal=Vue.ref(null);return}let momentVal=moment(val);momentVal.format=this.format;this.momentVal=Vue.ref(momentVal)},},methods:{weekChange(date){this.$emit('update:value',date.weekday(0).format('YYYY-MM-DD'));date.format=()=>{return this.format()}},openWeekChange(status){this.isOpen=status}},template:`<div><a-week-picker v-model:value="momentVal"type="date":placeholder="placeholder||'请选择周'"style="width: 100%;"@change="weekChange"@open-change="openWeekChange"></a-week-picker></div>`,});


        app.component('CurdShowField',{
            props:['field','info'],
            name:'CurdShowField',
            methods:{
                showImages(imgs, start){
                    if(parseInt(start)!=start){
                        if(start){
                            let arr=typeof imgs==='string'?imgs.split('|'):imgs;
                            let index=arr.indexOf(start);
                            if(index!==-1){
                                start=index;
                                imgs=arr;
                            }
                        }
                    }
                    window.top.showImages(imgs, start);
                },
            },
            template:`<div class="curd-show-field-box">
                            <div v-if="field.type==='ImagesField'">
                                <div class="img-box">
                                    <div class="img-box-item" v-for="(vo,key) in info[field.name+'Arr']" @click="showImages(info[field.name+'Arr'],key)">
                                        <img :src="vo" />
                                    </div>
                                </div>
                                <span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                            </div>
                            <div v-else-if="field.type==='MapRangeField'">
                                <map-range v-model:value="info[field.name]" :disabled="true"></map-range>
                            </div>
                            <div v-else-if="field.type==='MoreStringField'">
                                <ul class="more-string-box">
                                    <li class="more-string-item" v-for="(vo,key) in info[field.name+'Arr']">
                                        {{vo}}<span class="ext-box" v-if="field.ext">（{{field.ext}}）</span>
                                    </li>
                                </ul>
                            </div>
                            <div v-else-if="field.type==='ListField'">
                                <div class="list-field-box">
                                    <div class="list-field-item" v-for="(vo,key) in info[field.name+'List']">
                                        <div class="list-field-item-row" v-for="v in field.fields">
                                            <div class="list-field-item-row-l">{{v.title}}:</div>
                                            <div class="list-field-item-row-r"><curd-show-field :info="vo" :field="v"></curd-show-field></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else>
                                <div>{{info[field.name]}}<span class="ext-box" v-if="field.ext">（{{field.ext}}）</span></div>
                            </div>
                        </div>`,
        });

        /*** 公开表table组件 ***/
        app.component('CurdTable',{
            props:['childs','pagination','data','loading','listColumns','canEdit','actionWidth','canDel'],
            setup(props,ctx){
                const listColumns=props.listColumns;
                let groupTitles=[],columns=[],titleItems={},columnsCount=0;
                for(let groupTtitle in listColumns){
                    groupTitles.push(groupTtitle);
                    let column={title:groupTtitle,children:[]};
                    listColumns[groupTtitle].forEach(function(item){
                        let customTitle='customTitle-'+item.name;
                        titleItems[customTitle]=item;
                        let col={
                            dataIndex:item.name,
                            // title:item.title,
                            slots:{title:customTitle},
                            ellipsis:true,
                        };
                        if(item.listColumnWidth){
                            col.width=item.listColumnWidth;
                        }
                        switch (item.type){
                            case 'ImagesField':
                                col.slots.customRender='images';
                                break;
                            default:
                                col.slots.customRender='default-value';
                        }
                        columnsCount++;
                        column.children.push(col);
                    })
                    columns.push(column);
                }
                const isGroup=groupTitles.length>1||(!listColumns['']&&groupTitles.length>0);

                if(!isGroup){
                    columns=columns[0].children;
                }

                columns.push({
                    title:'创建时间',
                    ellipsis:true,
                    dataIndex: "create_time",
                    slots: { customRender: 'create-time' },
                    width:160,
                })
                columnsCount++;

                let actionW=70;
                if(props.childs){
                    props.childs.forEach(v=>{
                        actionW+=38+(v.listBtn.split('').length*9);
                    })
                }
                if(props.canEdit!==false){
                    actionW+=40;
                }
                if(props.canDel){
                    actionW+=32;
                }
                const oldActionW=actionW;
                const getActionWidthByProps=function (propActionWidth,oldActionW){
                    //自定义操作栏长度
                    if(propActionWidth){
                        if(typeof propActionWidth==='function'){
                            oldActionW=propActionWidth(oldActionW)
                        }else{
                            oldActionW+=propActionWidth;
                        }
                    }
                    return oldActionW;
                }
                //可prop动态设置宽度
                const newActionW=Vue.ref(getActionWidthByProps(props.actionWidth,oldActionW));
                columns.push({
                    title:'操作',
                    slots: { customRender: 'action' },
                    width:newActionW,
                    fixed: 'right',
                })
                columnsCount++;
                let id='pub-default-table-'+window.guid();

                //太小出现滚动条
                let scrollX=Vue.ref(undefined);
                let scrollY=Vue.ref(undefined);
                let getX=function(){
                    if(document.body.clientWidth>1640){
                        return undefined;
                    }
                    if(columnsCount<=5){
                        return document.body.clientWidth>650?undefined:960;
                    }
                    if(columnsCount<7){
                        return document.body.clientWidth>740?undefined:1080;
                    }
                    if(columnsCount<9){
                        return document.body.clientWidth>820?undefined:1240;
                    }
                    if(columnsCount<12){
                        return document.body.clientWidth>1460?undefined:1560;
                    }
                    return 1640;
                }
                let getY=async function(){
                    let h=undefined;
                    await Vue.nextTick(async function(){
                        let parent=document.getElementById(id).parentNode;
                        let elH=(parent.querySelector('.ant-table-body .ant-table-fixed')||parent.querySelector('.ant-table-body .ant-table-tbody')).clientHeight;
                        let theadH=(parent.querySelector('.ant-table-header .ant-table-fixed')||parent.querySelector('.ant-table-body .ant-table-thead')).clientHeight;
                        if(document.body.clientHeight>elH&&(parent.clientHeight-theadH)>elH){
                            return;
                        }
                        h=parent.clientHeight-theadH;
                    })
                    return h;
                };

                let onresize=()=>{
                    scrollX.value=getX();
                    getY().then(res=>{
                        scrollY.value=res;
                    })
                };
                onresize();
                window.onresize=onresize;

                return {
                    oldActionW,
                    actionW:newActionW,
                    getActionWidthByProps,
                    columns:Vue.ref(columns),
                    isGroup,
                    titleItems,
                    scrollX,
                    scrollY,
                    id
                }
            },
            watch: {
                actionWidth(val){
                    this.actionW=this.getActionWidthByProps(val,this.oldActionW);
                }
            },
            methods:{
                handleTableChange(pagination, filters, sorter){
                    this.$emit('change',pagination, filters, sorter,this.data)
                },
                openEdit(row){
                    this.$emit('openEdit',row)
                },
                openShow(row){
                    this.$emit('openShow',row)
                },
                openChildList(row,modelInfo){
                    this.$emit('openChildList',row,modelInfo)
                },
                showImages(imgs, start){
                    window.top.showImages(imgs, start);
                },
                onDelete(row){
                    this.$emit('onDelete',row)
                }
            },
            template:`<div :id="id">
                        <a-table
                            :row-key="record => record.id"
                            :columns="columns"
                            :data-source="data"
                            :pagination="pagination"
                            :loading="loading"
                            @change="handleTableChange"
                            class="curd-table"
                            :bordered="isGroup"
                            :scroll="{ x: scrollX ,y:scrollY}"
                        >
                            <template #[key] v-for="(item,key) in titleItems">
                                <div style="white-space:normal;line-height: 1.14">
                                <span>{{item.title}}</span>
                                <span v-if="item.ext" style="color: #bfbfbf">（{{item.ext}}）</span>
                                </div>
                            </template>
                            
                            <template #images="{text:val}">
                                <a-tooltip placement="topLeft" v-if="val">
                                    <template #title>查看图片</template>
                                    <a @click="showImages(val)"><file-image-outlined></file-image-outlined> 查看</a>
                                </a-tooltip>
                                <span v-else style="color: #f0f0f0">无</span>
                            </template>
                            
                              <template #default-value="{text:val}">
                                <a-tooltip placement="topLeft">
                                    <template #title>{{val}}</template>
                                    {{val}}
                                </a-tooltip>
                              </template>
                            
                               <template #create-time="{ text: create_time }">
                                    <a-tooltip>
                                        <template #title>{{ create_time }}</template>
                                        {{ create_time }}
                                    </a-tooltip>
                                </template>
                                
                                <template #action="{ record }">
                                    <slot name="do-before" :record="record">
                                   
                                    </slot>
                                    <slot name="do" :record="record">
                                        <a @click="openShow(record)">详细</a>
                                          
                                        <template  v-if="canEdit!==false">
                                            <a-divider type="vertical"></a-divider>
                                            <a @click="openEdit(record)">修改</a>
                                        </template>
                                       
                                        <template v-for="vo in childs">
                                            <a-divider type="vertical"></a-divider>
                                            <a @click="openChildList(record,vo)">{{vo.listBtn}}</a>
                                        </template>
                                    </slot>
                                     <slot name="do-after" :record="record">
                                     
                                    </slot>
                                    <template v-if="canDel">
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
                        </a-table>
                    </div>`,
        })
        app.mount('#app')
    };
});