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

            const centerArr=(props.center&&props.center!==','?props.center:'120.225639,30.165049').split(',');

            function initMap() {
                Vue.nextTick(() => {
                    let defCenter=new AMap.LngLat(centerArr[0], centerArr[1]);
                    //初始化地图对象
                    window.mapObjs[mapId].map = new AMap.Map(mapId,{
                        layers: [
                            // 卫星
                            new AMap.TileLayer.Satellite(),
                            // 路网
                            new AMap.TileLayer.RoadNet()
                        ],
                        center:defCenter,
                    });
                    //设置显示地图的中心点和级别
                    window.mapObjs[mapId].map.setZoomAndCenter(10,defCenter);  // 初始化地图,设置中心点坐标和地图级别


                   
                    window.mapObjs[mapId].map.on('complete', function () {
                        const polygonLngLat=props.value?JSON.parse(props.value):[];
                        const polygonArr= [];
                        polygonLngLat.forEach(v=>{
                            polygonArr.push(new AMap.LngLat(v[0],v[1]))
                        })
                        window.mapObjs[mapId].polygon =new AMap.Polygon({
                            path:polygonArr,
                            strokeColor : "#096dd9",
                            strokeWeight : 1.5,
                            strokeOpacity : 0.9,
                            fillColor: "#69c0ff",
                            fillOpacity: 0.3
                        });
                        window.mapObjs[mapId].map.addOverlay(window.mapObjs[mapId].polygon);
                        if(polygonArr&&polygonArr.length>0){
                            //显示最佳比例尺
                            // window.mapObjs[mapId].map.setViewport(polygonArr);
                            window.mapObjs[mapId].map.setFitView([window.mapObjs[mapId].polygon]);
                        }


                        isInited.value = true;
                    });
                })
            }

            function loadMap() {
                window._AMapSecurityConfig = {
                    securityJsCode:'604a1f9d93f234478330d19b97908c51',
                }
                require(['https://webapi.amap.com/maps?v=2.0&key=103028a8c0d4cbd05993f251c2a2ead7'+'&'],function (AMap){
                    window.AMap=AMap;

                    let css = document.createElement('style');
                    css.innerHTML = '.amap-logo,.amap-copyright{z-index:-1!important;opacity:0!important;height:0!important;}';
                    document.head.appendChild(css);
                    // AMap.plugin(['AMap.Geocoder', 'AMap.PlaceSearch','AMap.DistrictSearch','AMap.PolygonEditor'], () => {//异步同时加载多个插件
                    //     if (document.getElementById('app')) document.getElementById('app').style.display = 'block'
                    //     if (document.getElementById('app-loading')) document.getElementById('app-loading').style.display = 'none'
                    //     this.initMap()
                    // })
                    initMap();
                })
            }

            if (typeof AMap !== 'undefined' && typeof AMap.Map !== 'undefined') {
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
                    content: '/tpscriptvuecurd/field/map_range/AMap/map_open.html&center='+this.centerArr.join(',')+'&v='+v,
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
                            polygonArr.push(new AMap.LngLat(v[0],v[1]))
                        })
                        window.mapObjs[this.mapId].polygon.setPath(polygonArr)
                        // window.mapObjs[this.mapId].map.setViewport(polygonArr);
                        window.mapObjs[this.mapId].map.setFitView([window.mapObjs[this.mapId].polygon]);
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