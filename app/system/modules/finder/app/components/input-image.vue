<template>

    <a class="uk-placeholder uk-text-center uk-display-block uk-margin-remove" v-if="!source" v-on="click: pick()">
        <img width="60" height="60" alt="{{ 'Placeholder Image' | trans }}" v-attr="src: $url.static('app/system/assets/images/placeholder-image.svg')">
        <p class="uk-text-muted uk-margin-small-top">{{ 'Add image' | trans }}</p>
    </a>

    <div class="uk-panel uk-visible-hover uk-overlay-hover" v-if="source">

        <div class="uk-overlay pk-image-max-height">
            <img v-attr="src: $url.static(source)">
            <div class="uk-overlay-panel uk-overlay-background uk-overlay-fade"></div>
        </div>

        <a class="uk-position-cover" v-on="click: pick()"></a>

        <div class="uk-panel-badge pk-panel-badge uk-hidden">
            <ul class="uk-subnav pk-subnav-icon">
                <li><a class="pk-icon-delete pk-icon-hover" title="{{ 'Delete' | trans }}" data-uk-tooltip="{delay: 500}" v-on="click: remove()"></a></li>
            </ul>
        </div>

    </div>

    <v-modal v-ref="modal" large>

        <panel-finder root="{{ storage }}" v-ref="finder" modal="true"></panel-finder>

        <div class="uk-modal-footer uk-text-right">
            <button class="uk-button uk-button-link uk-modal-close" type="button">{{ 'Cancel' | trans }}</button>
            <button class="uk-button uk-button-primary" type="button" v-attr="disabled: !selectButton" v-on="click: select()">{{ 'Select' | trans }}</button>
        </div>

    </v-modal>

</template>

<script>

    module.exports = {

        props: ['source'],

        data: function () {
            return _.merge({}, $pagekit);
        },

        computed: {

            selectButton: function() {
                var selected = this.$.finder.getSelected();
                return selected.length === 1 && this.$.finder.isImage(selected[0])
            }

        },

        methods: {

            pick: function() {
                this.$.modal.open();
            },

            select: function() {
                this.source = this.$.finder.getSelected()[0];
                this.$.modal.close();
            },

            remove: function() {
                this.source = ''
            }

        },

        template: __vue_template__

    };

    Vue.component('input-image', module.exports);

</script>
