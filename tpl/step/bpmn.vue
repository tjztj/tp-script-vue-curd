<style>
body, html{
    padding: 0;
}
#app{
    background-color: transparent;
    padding: 24px;
}
#tool-box{
    position: fixed;
    top: 0;
    left: 0;
    display: flex;
    align-items: center;
}
.tool-item{
    display: flex;
    background-color: #fff;
    padding: 12px;
    border-radius: 6px;
    box-shadow: 0px 1px 2px -2px rgba(0, 0, 0, 0.16),0px 3px 6px 0 rgba(0, 0, 0, 0.12),0px 5px 12px 4px rgba(0, 0, 0, 0.09);
    align-items: center;
}
.tool-item{
    margin: 12px;
}
.tool-item-btns{
    display: flex;
    align-items: center;
}
.tool-item-btns{
    margin-left: 4px;
    border: 1px solid #bfbfbf;
    padding: 4px 8px;
    border-radius: 4px;
}
.show-val{
    padding: 0 8px;
    font-weight: bold;
    color: #222;
}
svg.icon{
    width: 1em;
    height: 1em;
    vertical-align: text-top;
    fill: currentColor;
    overflow: hidden;
}
.tool-item-btns svg.icon{
    cursor: pointer;
    transition: all .3s;
    color: #8c8c8c;
}
.tool-item-btns svg.icon:hover{
    color: #597ef7;
}
.item-enlarge,.item-narrow{
    line-height: 1em;
    height: 1em;
}
#mountNode>canvas{
    margin: 24px;
    background-color: #fff;
}
</style>

<div id="tool-box" v-show="isInit">
<div class="item-label-font tool-item">
    <div class="tool-item-text">节点对齐方式</div>
    <div class="tool-item-btns" style="padding: 0">
        <a-select v-model="align" @change="alignChange" size="small" :style="{width:'80px',backgroundColor:'#fff',border:0}" >
            <a-option value="UL">左上</a-option>
            <a-option value="UR">右上</a-option>
            <a-option value="DL">左下</a-option>
            <a-option value="DR">右下</a-option>
        </a-select>
    </div>
</div>
<div class="item-label-font tool-item">
    <div class="tool-item-text">节点文字</div>
    <div class="tool-item-btns">
        <div class="item-enlarge" v-html="svg1" @click="addNodeF" @mousedown="startChangAn(addNodeF,200)" @mouseup="endChangAn()" @mouseout="endChangAn()"></div>
        <div class="show-val">{{nodeFontSize}}</div>
        <div class="item-narrow" v-html="svg2" @click="subNodeF" @mousedown="startChangAn(subNodeF,200)" @mouseup="endChangAn()" @mouseout="endChangAn()"></div>
    </div>
</div>
<div class="edge-label-font tool-item">
    <div class="tool-item-text">条件文字</div>
    <div class="tool-item-btns">
        <div class="item-enlarge" v-html="svg1" @click="addEdgeF" @mousedown="startChangAn(addEdgeF,200)" @mouseup="endChangAn()" @mouseout="endChangAn()"></div>
        <div class="show-val">{{edgeFontSize}}</div>
        <div class="item-narrow" v-html="svg2" @click="subEdgeF" @mousedown="startChangAn(subEdgeF,200)" @mouseup="endChangAn()" @mouseout="endChangAn()"></div>
    </div>
</div>
<div class="window-label-font tool-item">
    <div class="tool-item-text">整体</div>
    <div class="tool-item-btns">
        <div class="item-enlarge" v-html="svg1" @click="addSize" @mousedown="startChangAn(addSize,40)" @mouseup="endChangAn()" @mouseout="endChangAn()"></div>
        <div class="show-val">{{size}}%</div>
        <div class="item-narrow" v-html="svg2" @click="subSize" @mousedown="startChangAn(subSize,40)" @mouseup="endChangAn()" @mouseout="endChangAn()"></div>
    </div>
</div>
<div class="window-label-font tool-item">
    <div class="tool-item-text">动画</div>
    <div class="tool-item-btns">
        <a-switch size="small" v-model="showAnimation"></a-switch>
    </div>
</div>
</div>
<div id="mountNode"></div>