define(['/tpscriptvuecurd/field/map_range/BMapGL/md5.js'], function (lmd5) {
    if(!window.md5){
        window.md5=lmd5.hex_md5;
    }
    return {
        props: ['value', 'disabled', 'placeholder','center','zIndex'],
        setup(props, ctx) {
            props.value = props.value || '';
            props.placeholder = props.placeholder || '还未选择位置';
            props.disabled = typeof props.disabled === 'undefined' ? false : props.disabled;
            let guid = window.guid();
            let mapId = 'componentMapInit' + guid;
            let isInited = Vue.ref(false);
            window.mapObjs = window.mapObjs || {};
            window.mapObjs[mapId] = {};
            window.mapObjs[mapId].mark=null;

            const centerArr=(props.center&&props.center!==','?props.center:'120.212402,30.168529').split(',');

            function setMark(lnglat){
                const arr=lnglat.split(',');
                const lngLat=new BMapGL.Point(arr[0], arr[1]);

                if(!window.mapObjs[mapId].mark){
                    window.mapObjs[mapId].mark=new BMapGL.Marker(lngLat);
                    window.mapObjs[mapId].map.addOverlay(window.mapObjs[mapId].mark);
                }
                window.mapObjs[mapId].mark.setPosition(lngLat)
                window.mapObjs[mapId].map.centerAndZoom(lngLat,18);
            }

            function initMap() {
                Vue.nextTick(() => {
                    //初始化地图对象
                    window.mapObjs[mapId].map = new BMapGL.Map(mapId);
                    //设置显示地图的中心点和级别
                    window.mapObjs[mapId].map.centerAndZoom(new BMapGL.Point(centerArr[0], centerArr[1]), 10);  // 初始化地图,设置中心点坐标和地图级别
                    window.mapObjs[mapId].map.enableScrollWheelZoom(true);     //开启鼠标滚轮缩放
                    window.mapObjs[mapId].map.setMapType(BMAP_EARTH_MAP);      // 设置地图类型为地球模式


                    if(props.value){
                        setMark(props.value)
                    }
                    window.mapObjs[mapId].map.addEventListener('tilesloaded', function () {
                        isInited.value = true;
                    });

                })
            }

            function loadMap() {
                const initFunc='initializeBmap'+guid.replaceAll('-','_');
                window[initFunc]=function (){
                    initMap()
                };
                let jsapi = document.createElement('script');
                jsapi.src = '//api.map.baidu.com/api?type=webgl&v=1.0&ak=ZjBuQWgLwuirL24Tvt6EIVZvG9zQXjdM&callback='+initFunc;
                document.head.appendChild(jsapi);
                let css = document.createElement('style');
                css.innerHTML = '.anchorBL{z-index:-1!important;opacity:0!important;height:0!important;}';
                document.head.appendChild(css);
            }

            if (typeof BMapGL !== 'undefined' && typeof BMapGL.Map !== 'undefined') {
                initMap();
            } else {
                loadMap();
            }
            return {
                isInited,
                mapId,
                setMark,
                centerArr,
            }
        },
        computed: {
            sizeStyle() {
                return {
                    width: this.width || '360px',
                    height: this.height || '180px',
                    zIndex:this.zIndex?this.zIndex:undefined,
                }
            },
        },
        methods: {
            openSelect() {
                if (!this.openBox) {
                    this.openBox = window.vueDefMethods.openBox;
                }
                let v=vueData.vueCurdDebug?((new Date()).valueOf()):vueData.vueCurdVersion;
                this.openBox({
                    title: '请选择相关地点',
                    offset: 'lt',
                    content: '/tpscriptvuecurd/field/coordinate/BMapGL/map_open.html&center='+this.centerArr.join(',')+'&v='+v,
                }).on('success', (layero, index) => {
                    let mapWindow;
                    if(layero.iframe&&layero.iframe.contentWindow){
                        mapWindow=layero.iframe.contentWindow;
                    }else{
                        mapWindow=layero.find('iframe')[0].contentWindow;
                    }

                    [
                        vueData.themCssPath
                    ].forEach(val=>{
                        if(!val)return;
                        let styleEl = mapWindow.document.createElement('link');
                        styleEl.setAttribute('rel', 'stylesheet');
                        styleEl.setAttribute('media','all');
                        styleEl.setAttribute('href', val);
                        mapWindow.document.head.appendChild(styleEl);
                    })


                    mapWindow.mapSelect = this.value;
                    mapWindow.mapCenter=this.centerArr;
                    if(mapWindow.initSelect){
                        mapWindow.initSelect();
                    }
                    mapWindow.onMapSelected = val => {
                        this.$emit('update:value',val);
                        this.$emit('change', val);
                        this.setMark(val);
                        layero.close();
                    }
                }).end();
            }
        },
        template: `<div style="position: relative" :style="sizeStyle">
                        <a-spin :loading="!isInited">
                            <div v-show="!disabled"><a-button size="small" style="position: absolute;right: 6px;top: 6px;z-index: 999" @click="openSelect">{{value===''?'选择':'更改'}}</a-button></div>
                            <div v-show="value===''" :style="{lineHeight:sizeStyle.height}" style="position: absolute;z-index: 998;background-color: rgba(0,0,0,.45);left: 0;right: 0;top: 0;bottom: 0;text-align: center;color:#fff">{{placeholder}}</div>
                            <div :id="mapId" :style="sizeStyle" style="border-radius: 6px;overflow: hidden"></div>
                        </a-spin>
                      </div>`
    }
});