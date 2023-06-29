define([], function () {
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

            const centerArr=(props.center&&props.center!==','?props.center:'120.12,30.19').split(',');

            function setMark(lnglat){
                const arr=lnglat.split(',');
                const lngLat=new T.LngLat(arr[0], arr[1]);
                if(!window.mapObjs[mapId].mark){
                    window.mapObjs[mapId].mark = new T.Marker(lngLat); // 创建点
                    const icon=new T.Icon({
                        iconUrl:'https://webapi.amap.com/theme/v1.3/markers/n/mark_rs.png',
                        iconAnchor:new T.Point(9,32)
                    })
                    window.mapObjs[mapId].mark.setIcon(icon)
                    // icon.setIconUrl('https://webapi.amap.com/theme/v1.3/markers/n/mark_rs.png');
                    window.mapObjs[mapId].map.addOverLay(window.mapObjs[mapId].mark);
                }else{
                    window.mapObjs[mapId].mark.setLngLat(lngLat)
                }
                window.mapObjs[mapId].map.centerAndZoom(lngLat,18);
            }

            function initMap() {
                Vue.nextTick(() => {
                    const imageURL = "http://t0.tianditu.gov.cn/img_w/wmts?" +
                        "SERVICE=WMTS&REQUEST=GetTile&VERSION=1.0.0&LAYER=img&STYLE=default&TILEMATRIXSET=w&FORMAT=tiles" +
                        "&TILEMATRIX={z}&TILEROW={y}&TILECOL={x}&tk=dd78fa8009cfc85f584b62039a504a61";
                    //创建自定义图层对象
                    const lay = new T.TileLayer(imageURL, {minZoom: 1, maxZoom: 18});
                    const config = {layers: [lay]};
                    //初始化地图对象
                    window.mapObjs[mapId].map = new T.Map(mapId, config);
                    //设置显示地图的中心点和级别
                    window.mapObjs[mapId].map.centerAndZoom(new T.LngLat(centerArr[0], centerArr[1]), 10);

                    if(props.value){
                        setMark(props.value)
                    }
                    isInited.value = true;
                })
            }

            function loadMap() {
                const checkHaveObjs = setInterval(() => {
                    if (typeof T !== 'undefined' && typeof T.Map !== 'undefined') {
                        clearInterval(checkHaveObjs);
                        initMap();
                    }
                }, 20)
                let jsapi = document.createElement('script');
                jsapi.src = 'https://api.tianditu.gov.cn/api?v=4.0&tk=dd78fa8009cfc85f584b62039a504a61';
                document.head.appendChild(jsapi);
                let css = document.createElement('style');
                css.innerHTML = '.tdt-control-copyright{display: none!important;}';
                document.head.appendChild(css);
            }

            if (typeof T !== 'undefined' && typeof T.Map !== 'undefined') {
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
                    content: '/tpscriptvuecurd/field/coordinate/map_open.html&center='+this.centerArr.join(',')+'&v='+v,
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
                        this.setMark(val)
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