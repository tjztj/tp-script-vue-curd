<?php

namespace tpScriptVueCurd\field;

use tpScriptVueCurd\tool\field_tpl\Edit;
use tpScriptVueCurd\tool\field_tpl\FieldTpl;
use tpScriptVueCurd\tool\field_tpl\Index;
use tpScriptVueCurd\tool\field_tpl\Show;

class UEditorField extends EditorField
{

    protected array $toolbar = [
        [
            "fullscreen",   // 全屏
            "source",       // 源代码
            "|",
            "undo",         // 撤销
            "redo",         // 重做
            "|",
            "bold",         // 加粗
            "italic",       // 斜体
            "underline",    // 下划线
            "fontborder",   // 字符边框
            "strikethrough",// 删除线
            "superscript",  // 上标
            "subscript",    // 下标
            "removeformat", // 清除格式
            "formatmatch",  // 格式刷
            "autotypeset",  // 自动排版
            "blockquote",   // 引用
            "pasteplain",   // 纯文本粘贴模式
            "|",
            "forecolor",    // 字体颜色
            "backcolor",    // 背景色
            "insertorderedlist",   // 有序列表
            "insertunorderedlist", // 无序列表
            "selectall",    // 全选
            "cleardoc",     // 清空文档
            "|",
            "rowspacingtop",// 段前距
            "rowspacingbottom",    // 段后距
            "lineheight",          // 行间距
            "|",
            "customstyle",         // 自定义标题
            "paragraph",           // 段落格式
            "fontfamily",          // 字体
            "fontsize",            // 字号
            "|",
            "directionalityltr",   // 从左向右输入
            "directionalityrtl",   // 从右向左输入
            "indent",              // 首行缩进
            "|",
            "justifyleft",         // 居左对齐
            "justifycenter",       // 居中对齐
            "justifyright",
            "justifyjustify",      // 两端对齐
            "|",
            "touppercase",         // 字母大写
            "tolowercase",         // 字母小写
            "|",
            "link",                // 超链接
            "unlink",              // 取消链接
            "anchor",              // 锚点
            "|",
            "imagenone",           // 图片默认
            "imageleft",           // 图片左浮动
            "imageright",          // 图片右浮动
            "imagecenter",         // 图片居中
            "|",
            "simpleupload",        // 单图上传
            "insertimage",         // 多图上传
            "emotion",             // 表情
            "scrawl",              // 涂鸦
            "insertvideo",         // 视频
            "attachment",          // 附件
            "insertframe",         // 插入Iframe
            "insertcode",          // 插入代码
            "pagebreak",           // 分页
            "template",            // 模板
            "background",          // 背景
            "formula",             // 公式
            "|",
            "horizontal",          // 分隔线
            "date",                // 日期
            "time",                // 时间
            "spechars",            // 特殊字符
            "wordimage",           // Word图片转存
            "|",
            "inserttable",         // 插入表格
            "deletetable",         // 删除表格
            "insertparagraphbeforetable",     // 表格前插入行
            "insertrow",           // 前插入行
            "deleterow",           // 删除行
            "insertcol",           // 前插入列
            "deletecol",           // 删除列
            "mergecells",          // 合并多个单元格
            "mergeright",          // 右合并单元格
            "mergedown",           // 下合并单元格
            "splittocells",        // 完全拆分单元格
            "splittorows",         // 拆分成行
            "splittocols",         // 拆分成列
            "|",
            "print",               // 打印
            "preview",             // 预览
            "searchreplace",       // 查询替换
        ]
    ];

    protected string $uploadUrl = '/tp-script-vue-curd/Ueditor/index';

    protected int $height=420;

    public static function componentUrl(): FieldTpl
    {
        $type = class_basename(static::class);
        return new FieldTpl($type,
            new Index($type, ''),
            new Show($type,'/tpscriptvuecurd/field/u_editor/show.js'),
            new Edit($type,'/tpscriptvuecurd/field/u_editor/edit.js')
        );
    }
}