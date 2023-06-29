var M = Object.defineProperty, H = Object.defineProperties;
var P = Object.getOwnPropertyDescriptors;
var C = Object.getOwnPropertySymbols;
var S = Object.prototype.hasOwnProperty, V = Object.prototype.propertyIsEnumerable;
var E = (o, e, f) => e in o ? M(o, e, {enumerable: !0, configurable: !0, writable: !0, value: f}) : o[e] = f,
    w = (o, e) => {
        for (var f in e || (e = {})) S.call(e, f) && E(o, f, e[f]);
        if (C) for (var f of C(e)) V.call(e, f) && E(o, f, e[f]);
        return o
    }, _ = (o, e) => H(o, P(e));
(function (o, e) {
    typeof exports == "object" && typeof module != "undefined" ? e(exports, require("@wangeditor/editor")) : typeof define == "function" && define.amd ? define(["exports", "@wangeditor/editor"], e) : (o = typeof globalThis != "undefined" ? globalThis : o || self, e(o.WangEditorForVue = {}, o.editor))
})(this, function (o, f) {
    "use strict";

    const e=Vue;
    function a(t) {
        let n = `\u8BF7\u4F7F\u7528 '@${t}' \u4E8B\u4EF6\uFF0C\u4E0D\u8981\u653E\u5728 props \u4E2D`;
        return n += `
Please use '@${t}' event instead of props`, n
    }

    var h = (t, n) => {
        for (const [l, i] of n) t[l] = i;
        return t
    };
    const y = e.defineComponent({
        props: {
            mode: {type: String, default: "default"},
            defaultContent: {type: Array, default: []},
            defaultHtml: {type: String, default: ""},
            defaultConfig: {type: Object, default: {}},
            modelValue: {type: String, default: ""}
        }, setup(t, n) {
            const l = e.ref(null), i = e.shallowRef(null), s = e.ref(""), m = () => {
                if (!l.value) return;
                const d = e.toRaw(t.defaultContent);
                f.createEditor({
                    selector: l.value,
                    mode: t.mode,
                    content: d || [],
                    html: t.defaultHtml || t.modelValue || "",
                    config: _(w({}, t.defaultConfig), {
                        onCreated(r) {
                            if (i.value = r, n.emit("onCreated", r), t.defaultConfig.onCreated) {
                                const u = a("onCreated");
                                throw new Error(u)
                            }
                        }, onChange(r) {
                            const u = r.getHtml();
                            if (s.value = u, n.emit("update:modelValue", u), n.emit("onChange", r), t.defaultConfig.onChange) {
                                const c = a("onChange");
                                throw new Error(c)
                            }
                        }, onDestroyed(r) {
                            if (n.emit("onDestroyed", r), t.defaultConfig.onDestroyed) {
                                const u = a("onDestroyed");
                                throw new Error(u)
                            }
                        }, onMaxLength(r) {
                            if (n.emit("onMaxLength", r), t.defaultConfig.onMaxLength) {
                                const u = a("onMaxLength");
                                throw new Error(u)
                            }
                        }, onFocus(r) {
                            if (n.emit("onFocus", r), t.defaultConfig.onFocus) {
                                const u = a("onFocus");
                                throw new Error(u)
                            }
                        }, onBlur(r) {
                            if (n.emit("onBlur", r), t.defaultConfig.onBlur) {
                                const u = a("onBlur");
                                throw new Error(u)
                            }
                        }, customAlert(r, u) {
                            if (n.emit("customAlert", r, u), t.defaultConfig.customAlert) {
                                const c = a("customAlert");
                                throw new Error(c)
                            }
                        }, customPaste: (r, u) => {
                            if (t.defaultConfig.customPaste) {
                                const g = a("customPaste");
                                throw new Error(g)
                            }
                            let c;
                            return n.emit("customPaste", r, u, g => {
                                c = g
                            }), c
                        }
                    })
                })
            };

            function D(d) {
                const r = i.value;
                r != null && r.setHtml(d)
            }

            return e.onMounted(() => {
                m()
            }), e.watch(() => t.modelValue, d => {
                d !== s.value && D(d)
            }), {box: l}
        }
    }), b = {ref: "box", style: {height: "100%"}};

    function F(t, n, l, i, s, m) {
        return e.openBlock(), e.createElementBlock("div", b, null, 512)
    }

    var $ = h(y, [["render", F]]);
    const B = e.defineComponent({
        props: {
            editor: {type: Object},
            mode: {type: String, default: "default"},
            defaultConfig: {type: Object, default: {}}
        }, setup(t) {
            const n = e.ref(null), l = i => {
                if (!!n.value) {
                    if (i == null) throw new Error("Not found instance of Editor when create <Toolbar/> component");
                    i.toolbar=f.DomEditor.getToolbar(i) || f.createToolbar({
                        editor: i,
                        selector: n.value || "<div></div>",
                        mode: t.mode,
                        config: t.defaultConfig
                    })
                }
            };
            return e.watchEffect(() => {
                const {editor: i} = t;
                i != null && l(i)
            }), {selector: n}
        }
    }), p = {ref: "selector"};

    function T(t, n, l, i, s, m) {
        return e.openBlock(), e.createElementBlock("div", p, null, 512)
    }

    var v = h(B, [["render", T]]);
    o.Editor = $, o.Toolbar = v, Object.defineProperty(o, "__esModule", {value: !0}), o[Symbol.toStringTag] = "Module"
});
//# sourceMappingURL=index.js.map
