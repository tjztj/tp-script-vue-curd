{if $bgColor}
<style>body{background-color: {$bgColor};}</style>
{/if}
<style>
#app{
    margin: {$margin};
    padding: {$padding};
    padding-left:{$formLeft};
    max-width:{$formMaxWidth};
}
.vuecurd-def-box .ext-span{
    display: inline-block;
    margin-top: 5px;
}
</style>
{if $title}<h2 style="padding: 24px;margin: 0;font-size: 18px;font-weight: bold">{$title}</h2>{/if}
<div class="vuecurd-def-box vuecurd-show-def-box" style="padding: 6px 0 36px 0">

    <template v-for="(groupFieldItems,groupTitle) in groupFields">
        <fieldset class="field-group-fieldset" :class="{'show-group':haveGroup}">
            <div class="legend-box">
                <legend>{{groupTitle}}</legend>
            </div>
            <div class="show-group-field-rows" :class="{'is-grid-box':!!groupGrids[groupTitle]}" :style="gridStyle(groupTitle)">
                <template v-for="field in groupFieldItems">
                    <a-row class="row" v-if="!field.showUseComponent" :style="groupGrids[groupTitle]?fieldStyle(field,groupTitle):{}">
                        <a-col class="l" v-bind="groupGrids[groupTitle]?field.editLabelCol:{span:4}">
                            {{field.title}}：
                        </a-col>
                        <a-col class="r" v-bind="groupGrids[groupTitle]?field.editWrapperCol:{span:20}">
                            <curd-show-field :field="field" :info="info"></curd-show-field>
                        </a-col>
                    </a-row>
                    <component
                        v-else-if="fieldComponents['VueCurdShow'+field.type]"
                        :is="'VueCurdShow'+field.type"
                        :field="field"
                        :info="info"
                        :style="fieldStyle(field,groupTitle)"
                    ></component>
                </template>
            </div>
        </fieldset>
    </template>


</div>