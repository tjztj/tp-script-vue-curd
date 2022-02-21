define(['vueAdmin','g6'], function (va,G6) {
    let actions = {};
    ///////////////////////////////////////////////////////////////////////////////////////////////


    actions.bpmn=function (){
        let graph=null;
        return {
            data(){
                return {

              }
            },
            mounted() {
                this.pageIsInit();
                this.init()
                window.onresize= ()=>{
                    if(graph){
                        graph.destroy();
                        this.init()
                    }
                };
            },

            methods:{
                init(){
                    graph = new G6.Graph({
                        container: 'mountNode', // String | HTMLElement，必须，在 Step 1 中创建的容器 id 或容器本身
                        width: document.documentElement.clientWidth-54, // Number，必须，图的宽度
                        height: document.documentElement.clientHeight-62, // Number，必须，图的高度
                        fitView: true,
                        animate:true,
                        modes: {
                            default: ['drag-canvas', 'drag-node'],
                        },
                        layout: {
                            type: 'dagre',
                            rankdir: 'LR',
                            align: 'UL',
                            controlPoints: true,
                            nodesepFunc: () => 1,
                            ranksepFunc: () => 1,
                        },
                        defaultNode: {
                            size: [30, 20],
                            type: 'rect',
                            style: {
                                lineWidth: 2,
                                stroke: '#5B8FF9',
                                fill: '#C6E5FF',
                            },
                            labelCfg:{
                                style:{
                                    fontSize:4,
                                }
                            },
                        },
                        defaultEdge: {
                            type: 'polyline',
                            size: 1,
                            color: '#e2e2e2',
                            style: {
                                endArrow: {
                                    path: 'M 0,0 L 8,4 L 8,-4 Z',
                                    fill: '#e2e2e2',
                                },
                                radius: 20,
                            },
                            labelCfg:{
                                style:{
                                    fontSize:4,
                                }
                            },
                        },
                    });




                    graph.data(vueData.data); // 读取 Step 2 中的数据源到图上
                    graph.render(); // 渲染图
                },
            },
        }
    };


    ///////////////////////////////////////////////////////////////////////////////////////////////
    let return_actions = {};
    if(!actions[window.VUE_CURD.ACTION]&&window.ACTION){
        //方法可以直接写在页面中，不用写在这个js中也可以
        actions[window.VUE_CURD.ACTION]=window.ACTION;
    }
    for (let i in actions) return_actions[i] = function () {
        va(actions[i]())
    }
    return return_actions;
});