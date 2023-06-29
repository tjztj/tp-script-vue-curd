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

            const centerArr=(props.center&&props.center!==','?props.center:'120.225639,30.165049').split(',');

            function setMark(lnglat){
                const arr=lnglat.split(',');
                const lngLat=new AMap.LngLat(arr[0], arr[1]);

                if(!window.mapObjs[mapId].mark){
                    window.mapObjs[mapId].mark=new AMap.Marker(lngLat);
                    window.mapObjs[mapId].mark.setMap(window.mapObjs[mapId].map);
                }
                window.mapObjs[mapId].mark.setPosition(lngLat)
                window.mapObjs[mapId].map.setZoomAndCenter(18,lngLat);
            }

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


                    if(props.value){
                        setMark(props.value)
                    }
                    window.mapObjs[mapId].map.on('complete', function () {
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
                    content: '/tpscriptvuecurd/field/coordinate/AMap/map_open.html&center='+this.centerArr.join(',')+'&v='+v,
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