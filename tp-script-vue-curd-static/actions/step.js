define(['vueAdmin','g6'], function (va,G6) {
    let actions = {};
    ///////////////////////////////////////////////////////////////////////////////////////////////


    actions.bpmn=function (){
        G6.registerEdge(
            'polyline-running',
            {
                afterDraw(cfg, group) {
                    // get the first shape in the group, it is the edge's path here=
                    const shape = group.get('children')[0];
                    // the start position of the edge's path
                    const startPoint = shape.getPoint(0);

                    // add red circle shape
                    const circle = group.addShape('circle', {
                        attrs: {
                            x: startPoint.x,
                            y: startPoint.y,
                            fill: '#722ed1',
                            r: 1,
                        },
                        name: 'circle-shape',
                    });

                    // animation for the red circle
                    circle.animate(
                        (ratio) => {
                            // the operations in each frame. Ratio ranges from 0 to 1 indicating the prograss of the animation. Returns the modified configurations
                            // get the position on the edge according to the ratio
                            const tmpPoint = shape.getPoint(ratio);
                            // returns the modified configurations here, x and y here
                            return {
                                x: tmpPoint.x,
                                y: tmpPoint.y,
                            };
                        },
                        {
                            repeat: true, // Whether executes the animation repeatly
                            duration: 5000, // the duration for executing once
                        },
                    );
                },
            },
            'polyline', // extend the built-in edge 'cubic'
        );




        let graph=null,changan=null,changanIng=false;
        return {
            data(){
                return {
                    svg1:`<svg t="1645753872304" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4793"><path d="M64.48671 349.378226l895.354038 0 0 325.583287-895.354038 0 0-325.583287Z" p-id="4794"></path><path d="M349.372086 64.493873l325.583287 0 0 895.354038-325.583287 0 0-895.354038Z" p-id="4795"></path></svg>`,
                    svg2:`<svg t="1645753933844" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2552"><path d="M64.322981 349.208357l895.353015 0 0 325.583287-895.353015 0 0-325.583287Z" p-id="2553"></path></svg>`,
                    nodeFontSize:4,
                    edgeFontSize:4,
                    size:100,
                    isInit:false,
                    defWidth:document.documentElement.clientWidth-54,
                    defHeight:document.documentElement.clientHeight-62,
                    showAnimation:true,
                    align:undefined,
                }
            },
            watch:{
                showAnimation(){
                    graph.render(); // 渲染图
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
                getSize(){
                    return [parseInt(this.defWidth*(this.size/100)),parseInt(this.defHeight*(this.size/100))];
                },
                init(){
                    const size=this.getSize();
                    graph = new G6.Graph({
                        container: 'mountNode', // String | HTMLElement，必须，在 Step 1 中创建的容器 id 或容器本身
                        width: size[0], // Number，必须，图的宽度
                        height: size[1], // Number，必须，图的高度
                        fitView: true,
                        animate:true,
                        modes: {
                            default: ['drag-canvas', 'drag-node'],
                        },
                        layout: {
                            type: 'dagre',
                            rankdir: 'LR',
                            // align: 'UL',
                            controlPoints: true,
                            nodesepFunc: () => 8,
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
                        },
                        defaultEdge: {
                            type: 'polyline-running',
                            // type: 'polyline',
                            size: 1,
                            color: '#e2e2e2',
                            style: {
                                endArrow: {
                                    path: 'M 0,0 L 8,4 L 8,-4 Z',
                                    fill: '#e2e2e2',
                                },
                                radius: 20,
                            },
                        },
                    });


                    graph.node((node) => {
                        return {
                            labelCfg:{
                                style:{
                                    fontSize:this.nodeFontSize,
                                }
                            },
                        };
                    });
                    graph.edge((edge) => {
                        return {
                            type:this.showAnimation?'polyline-running':'polyline',
                            labelCfg:{
                                style:{
                                    fontSize:this.edgeFontSize,
                                }
                            },
                        };
                    });
                    graph.data(vueData.data); // 读取 Step 2 中的数据源到图上
                    graph.render(); // 渲染图
                    this.isInit=true;
                },

                addNodeF(){
                    this.nodeFontSize++;
                    graph.render(); // 渲染图
                },
                subNodeF(){
                    if(this.nodeFontSize<=1)return;
                    this.nodeFontSize--;
                    graph.render(); // 渲染图
                },
                addEdgeF(){
                    this.edgeFontSize++;
                    graph.render(); // 渲染图
                },
                subEdgeF(){
                    if(this.edgeFontSize<=1)return;
                    this.edgeFontSize--;
                    graph.render(); // 渲染图
                },
                addSize(){
                    this.size++;
                    graph.changeSize(...this.getSize());
                    graph.render();
                },
                subSize(){
                    if(this.size<=1)return;
                    this.size--;
                    graph.changeSize(...this.getSize());
                    graph.render();
                },
                startChangAn(func,step){
                    if(changan){
                        window.clearInterval(changan);
                    }
                    changanIng=true;
                    setTimeout(()=>{
                        if(changanIng){
                            changan=setInterval(func,40)
                        }
                    },step||120)
                },
                endChangAn(){
                    if(changan){
                        window.clearInterval(changan);
                    }
                    changan=null;
                    changanIng=false;
                },
                alignChange(){
                    graph.updateLayout({
                        align:this.align
                    })
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