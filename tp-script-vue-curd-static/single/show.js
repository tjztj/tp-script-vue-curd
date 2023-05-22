define(['vueAdmin'], function (va) {
    let actions = {};

    actions[vueData.vueCurdAction]=function(){

        return {
            data(){
                return {
                    info:vueData.info,
                    haveGroup:vueData.groupFields?true:false,
                    groupFields:vueData.groupFields||{'':vueData.fields},
                    groupGrids:vueData.groupGrids||{},
                    fieldComponents,
                }
            },
            methods:{
                checkShowGroup(groupFieldItems){
                    for(let i in groupFieldItems){
                        if(groupFieldItems[i].editShow){
                            return true;
                        }
                    }
                    return false;
                },
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
                gridStyle(title){
                    const style={};
                    if(!this.groupGrids[title]){
                        return style;
                    }

                    for(let i in this.groupGrids[title]){
                        if(this.groupGrids[title][i]){
                            style[i]=this.groupGrids[title][i];
                        }
                    }
                    if(Object.keys(style).length>0){
                        style.display='grid';
                    }
                    return style;
                },
                fieldStyle(field,groupTitle){
                    const style={};
                    if(!field.grid||!this.groupGrids[groupTitle]){
                        return style;
                    }
                    for(let i in field.grid){
                        if(field.grid[i]){
                            style[i]=field.grid[i];
                        }
                    }
                    return style;
                },
            }

        }
    }



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