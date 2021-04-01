define([],function(){
    let regions={};
    return {
        props:['field','value','validateStatus','form'],
        setup(props,ctx){

            let level_3=false;
            props.field.regionTree.forEach(v=>{
                regions[parseInt(v.id)]=v;
                if(v.children){
                    v.children.forEach(val=>{
                        regions[parseInt(val.id)]=val;
                        if(val.children){
                            val.children.forEach(vo=>{
                                regions[parseInt(vo.id)]=vo;
                                level_3=true;
                            })
                        }
                    })
                }
            })


            if(!props.field.readOnly&&props.field.editShow===true&&props.field.required===true&&!props.form.id&&!props.value){
                //如果是添加，且是必填，且为空
                if(props.field.regionTree.length===1){
                    if(!props.field.regionTree[0].children||props.field.regionTree[0].children.length===0){
                        //todo
                        ctx.emit('update:value',[props.field.regionTree[0].id])
                    }else if(props.field.regionTree[0].children.length===1){
                        if(level_3&&props.field.regionTree[0].children[0].children){
                            if(props.field.regionTree[0].children[0].children.length===1){
                                ctx.emit('update:value',[props.field.regionTree[0].id,props.field.regionTree[0].children[0].id,props.field.regionTree[0].children[0].children[0].id])
                            }
                        }else{
                            ctx.emit('update:value',[props.field.regionTree[0].id,props.field.regionTree[0].children[0].id])
                        }
                    }
                }
            }

            return {
                level_3
            }
        },
        computed:{
            modelVal:{
                get(){
                    if(this.field.canEdit===false||typeof this.value==='undefined'||typeof this.value==='object'){
                        return this.value;
                    }
                    if(this.value&&(typeof this.value==='string'||typeof this.value==='number')){
                        const vals=this.value.toString().split(',').map(v=>parseInt(v));
                        if(vals.length===1&&regions[vals[0]]&&!regions[vals[0]].children){
                            if(this.level_3){
                                return [regions[regions[vals[0]].pid].pid,regions[vals[0]].pid,vals[0]];
                            }
                            return [regions[vals[0]].pid,vals[0]];
                        }
                        return vals
                    }
                    return [];
                },
                set(val){
                    this.$emit('update:value',val);
                }
            }
        },
        methods:{
            onRegionChange(){
                this.$emit('update:validateStatus','success');
            },
        },
        template:`<div class="field-box">
                   <template v-if="form.id&&field.canEdit===false">
                        <div class="l">
                            <span v-if="form[field.pField]">{{form[field.pField]}}/</span>{{form[[field.cField]]}}
                        </div>
                    </template>
                    <template v-else>
                        <div class="l">
                            <a-cascader
                                v-model:value="modelVal"
                                :options="field.regionTree"
                                :placeholder="field.placeholder||'请选择村社'"
                                show-search
                                :disabled="field.readOnly"
                                change-on-select="canCheckParent"
                                @change="onRegionChange"
                            />
                        </div>
                        <div class="r">
                            <span v-if="field.ext" class="ext-span">{{ field.ext }}</span>
                        </div>
                    </template>
                </div>`,
    }
});