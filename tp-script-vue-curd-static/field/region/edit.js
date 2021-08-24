define([],function(){
    let regions={},regionValues={},textValues={};
    let level=1;

    function getDataTree(field){
        if(!field.multiple){
            return field.regionTree;
        }
        if(field.canCheckParent){
            return field.regionTree;
        }

        const setParentDisable=function(arr){
            for(let i in arr){
                const havChild=arr[i].children.length>0;
                if(typeof arr[i].disableCheckbox==='undefined'){
                    arr[i].disableCheckbox=havChild;
                }
                if(typeof arr[i].disabled==='undefined'){
                    arr[i].disabled=havChild;
                }
                if(havChild){
                    arr[i].children=setParentDisable(arr[i].children)
                }
            }
            return arr;
        }
        return setParentDisable(field.regionTree);
    }
    return {
        props:['field','value','validateStatus','form','info'],
        setup(props,ctx){
            let justOne=true;
            props.field.regionTree.forEach(v=>{
                regions[parseInt(v.id)]=v;
                regionValues[parseInt(v.id)]=[parseInt(v.id)];
                textValues['/'+v.name]=regionValues[parseInt(v.id)];
                if(v.children){
                    if(v.children.length>1){
                        justOne=false;
                    }
                    v.children.forEach(val=>{
                        if(level===1){
                            level=2;
                        }
                        regions[parseInt(val.id)]=val;
                        regionValues[parseInt(val.id)]=[parseInt(v.id),parseInt(val.id)];
                        textValues[v.name+'/'+val.name]=regionValues[parseInt(val.id)];
                        if(val.children){
                            if(val.children.length>1){
                                justOne=false;
                            }
                            val.children.forEach(vo=>{
                                if(level!==3){
                                    level=3;
                                }
                                regions[parseInt(vo.id)]=vo;
                                regionValues[parseInt(vo.id)]=[parseInt(v.id),parseInt(val.id),parseInt(vo.id)];
                                textValues[val.name+'/'+vo.name]=regionValues[parseInt(vo.id)];
                                level_3=true;
                            })
                        }
                    })
                }
            })


            if(!props.field.readOnly&&props.field.editShow===true&&props.field.required===true&&!props.form.id&&!props.value){
                //如果是添加，且是必填，且为空
                if(justOne){
                    ctx.emit('update:value', regionValues[Object.keys(regionValues)[Object.keys(regionValues).length - 1]]);
                }
            }else{
                if(props.form.id){
                    if(props.form[props.field.name]&&!/\d+/.test(props.form[props.field.name])){
                        ctx.emit('update:value', props.info['_Original_'+props.field.name]);
                    }
                }
            }


            return {
                dataTree:getDataTree(props.field),
            }
        },
        computed:{
            showSelected(){
                return this.form.id&&this.field.canEdit===false;
            },
            modelVal:{
                get(){
                    if(this.showSelected||typeof this.value==='undefined'||typeof this.value==='object'){
                        return this.value;
                    }
                    if(typeof this.value==='string'||typeof this.value==='number'){
                        if(this.value){
                            const vals=this.getDefValue();
                            if(vals){
                                this.$emit('update:value', this.field.multiple?vals.map(v=>v.value).join(','):vals);
                                return vals;
                            }
                        }
                    }
                    return [];
                },
                set(val){
                    this.$emit('update:value',this.field.multiple?val.map(v=>v.value).join(','):val);
                },
            }
        },
        methods:{
            onRegionChange(){
                this.$emit('update:validateStatus','success');
            },
            getDefValue(){
                if(/^\d+$/.test(this.value.toString())){
                    if(this.field.multiple){
                        return [regions[this.value]]
                    }else{
                        const vals=this.value.toString().split(',').map(v=>parseInt(v));
                        return regionValues[vals[vals.length-1]];
                    }
                }else{
                    if(this.field.multiple){
                        return this.value.toString().split(',').map(v=>regions[parseInt(v)]);
                    }else{
                        let str='';
                        if(level>1&&this.field.pField&&this.form[this.field.pField]){
                            str+=this.form[this.field.pField];
                        }
                        str+='/'+(this.field.cField?this.form[[this.field.cField]]:this.value);
                        return textValues[str];
                    }
                }
            },

        },
        template:`<div class="field-box">
                   <template v-if="showSelected">
                        <div class="l">
                            <template v-if="field.cField">
                                <span v-if="field.pField&&info[field.pField]">{{info[field.pField]}}/</span>{{info[field.cField]}}
                            </template>
                            <template v-else>
                                {{info[field.name]}}
                            </template>
                        </div>
                    </template>
                    <template v-else>
                        <div class="l">
                            <a-tree-select v-if="field.multiple"
                                 v-model:value="modelVal"
                                 :disabled="field.readOnly"
                                 :tree-data="dataTree"
                                 :replace-fields="{children:'children', title:'label', key:'value', value: 'value' }"
                                 :tree-default-expand-all="dataTree.length===1"
                                 tree-node-filter-prop="label"
                                 multiple
                                 allow-clear
                                 show-search
                                 style="width: 100%"
                                 tree-checkable
                                 tree-check-strictly
                                 :placeholder="field.placeholder||'请选择村社'"
                                 :dropdown-style="{ maxHeight: '350px', overflow: 'auto' }"
                            >
                            
                            </a-tree-select>
                            <a-cascader v-else
                                v-model:value="modelVal"
                                :options="dataTree"
                                :placeholder="field.placeholder||'请选择村社'"
                                show-search
                                :disabled="field.readOnly"
                                :change-on-select="field.canCheckParent"
                                @change="onRegionChange"
                            ></a-cascader>
                        </div>
                        <div class="r">
                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                        </div>
                    </template>
                </div>`,
    }
});
