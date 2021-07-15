define([],function(){
    return {
        props:['info','field'],
        setup(props, ctx) {
            // console.log(props.info[props.field.name]);
        },
        template:`<div>
                    <div v-html="info[field.name]"></div>
                </div>`,
    }
});