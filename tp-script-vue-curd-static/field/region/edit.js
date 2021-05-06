define([],function(){
    let regions={},regionValues={},textValues={};
    return {
        props:['field','value','validateStatus','form'],
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
                        regions[parseInt(val.id)]=val;
                        regionValues[parseInt(val.id)]=[parseInt(v.id),parseInt(val.id)];
                        textValues[v.name+'/'+val.name]=regionValues[parseInt(val.id)];
                        if(val.children){
                            if(val.children.length>1){
                                justOne=false;
                            }
                            val.children.forEach(vo=>{
                                regions[parseInt(vo.id)]=vo;
                                regionValues[parseInt(vo.id)]=[parseInt(v.id),parseInt(val.id),parseInt(vo.id)];
                                textValues[val.name+'/'+vo.name]=regionValues[parseInt(vo.id)];
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
            }
        },
        computed:{
            modelVal:{
                get(){
                    if(this.field.canEdit===false||typeof this.value==='undefined'||typeof this.value==='object'){
                        if(typeof this.value==='string'||typeof this.value==='number'){
                            if(this.value&&!/^\d+$/.test(this.value.toString())){
                                let str='';
                                if(this.field.pField&&this.form[this.field.pField]){
                                    str+=this.form[this.field.pField];
                                }
                                str+='/'+(this.field.cField?this.form[[this.field.cField]]:this.value);
                                if(textValues[str]){
                                    this.$emit('update:value', textValues[str]);
                                    return textValues[str]
                                }
                            }
                        }
                        return this.value;
                    }
                    if(this.value&&(typeof this.value==='string'||typeof this.value==='number')){
                        const vals=this.value.toString().split(',').map(v=>parseInt(v));
                        return regionValues[vals[vals.length-1]];
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
                                :change-on-select="field.canCheckParent"
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
