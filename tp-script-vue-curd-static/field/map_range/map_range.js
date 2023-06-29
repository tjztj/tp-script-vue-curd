define([], function () {
    return {
        props: ['value', 'disabled', 'placeholder','district','center','zIndex'],
        setup(props, ctx) {
            props.value = props.value || '';
            props.placeholder = props.placeholder || '还未选择区域';
            props.disabled = typeof props.disabled === 'undefined' ? false : props.disabled;
            let guid = window.guid();
            let mapId = 'componentMapInit' + guid;
            let isInited = Vue.ref(false);
            window.mapObjs = window.mapObjs || {};
            window.mapObjs[mapId] = {};

            const centerArr=(props.center&&props.center!==','?props.center:'120.12,30.19').split(',');

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



                    const polygonLngLat=props.value?JSON.parse(props.value):[];
                    const polygonArr= [];
                    polygonLngLat.forEach(v=>{
                        polygonArr.push(new T.LngLat(v[0],v[1]))
                    })
                    window.mapObjs[mapId].polygon =new T.Polygon(polygonArr,{
                        color: "#096dd9",
                        weight: 1.5,
                        opacity: 0.9,
                        fillColor: "#69c0ff",
                        fillOpacity: 0.3
                    });
                    window.mapObjs[mapId].map.addOverLay(window.mapObjs[mapId].polygon);
                    if(polygonArr&&polygonArr.length>0){
                        //显示最佳比例尺
                        window.mapObjs[mapId].map.setViewport(polygonArr);
                    }

                    isInited.value = true;
                })
            }

            function loadMap() {
                const checkHaveObjs=setInterval(()=>{
                    if(typeof T!=='undefined'&&typeof T.Map!=='undefined'){
                        clearInterval(checkHaveObjs);
                        initMap();
                    }
                },20)
                let jsapi = document.createElement('script');
                jsapi.src = 'https://api.tianditu.gov.cn/api?v=4.0&tk=dd78fa8009cfc85f584b62039a504a61';
                document.head.appendChild(jsapi);
                let css = document.createElement('style');
                css.innerHTML = '.tdt-control-copyright{display: none!important;}';
                document.head.appendChild(css);
            }

            if (typeof T!=='undefined'&&typeof T.Map!=='undefined') {
                initMap();
            } else {
                loadMap();
            }
            return {
                isInited,
                mapId,
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
            paths() {
                return this.value ? JSON.parse(this.value) : [];
            }
        },
        methods: {
            openSelect() {
                if(!this.openBox){
                    this.openBox= window.vueDefMethods.openBox;
                }
                let v=vueData.vueCurdDebug?((new Date()).valueOf()):vueData.vueCurdVersion;
                this.openBox({
                    title: '请绘制相关区域',
                    offset: 'lt',
                    content: '/tpscriptvuecurd/field/map_range/map_open.html&center='+this.centerArr.join(',')+'&v='+v,
                }).on('success', (layero, index) => {
                    let mapWindow;
                    if(layero.iframe&&layero.iframe.contentWindow){
                        mapWindow=layero.iframe.contentWindow;
                    }else{
                        mapWindow=layero.find('iframe')[0].contentWindow;
                    }

                    let v=vueData.vueCurdDebug?((new Date()).valueOf()):vueData.vueCurdVersion;
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

                    mapWindow.mapRangeSelectedPaths = JSON.stringify(this.paths);
                    mapWindow.mapCenter=this.centerArr;
                    mapWindow.mapDistrict=this.district;
                    mapWindow.isWindowSuccessInit=true;
                    if(mapWindow.windowSuccessInit){
                        mapWindow.windowSuccessInit();
                    }
                    mapWindow.onMapRangeSelected = (pathArr) => {
                        this.$emit('update:value', JSON.stringify(pathArr));
                        this.$emit('change', pathArr);
                        const polygonArr= [];
                        pathArr.forEach(v=>{
                            polygonArr.push(new T.LngLat(v[0],v[1]))
                        })
                        window.mapObjs[this.mapId].polygon.setLngLats(polygonArr)
                        window.mapObjs[this.mapId].map.setViewport(polygonArr);
                        layero.close();
                    }
                }).end();
            }
        },
        template: `<div style="position: relative" :style="sizeStyle">
                        <a-spin :loading="!isInited">
                            <div v-show="!disabled"><a-button size="small" style="position: absolute;right: 6px;top: 6px;z-index: 1031" @click="openSelect">{{paths.length<3?'选择':'更改'}}</a-button></div>
                            <div v-show="paths.length<3" :style="{lineHeight:sizeStyle.height}" style="position: absolute;z-index: 1030;background-color: rgba(0,0,0,.45);left: 0;right: 0;top: 0;bottom: 0;text-align: center;color:#fff">{{placeholder}}</div>
                            <div :id="mapId" :style="sizeStyle" style="border-radius: 6px;overflow: hidden"></div>
                        </a-spin>
                      </div>`
    }
});