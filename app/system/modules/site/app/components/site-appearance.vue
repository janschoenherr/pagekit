<template>

    <div class="uk-form-horizontal">

        <div class="uk-form-row">
            <span class="uk-form-label">{{ 'Title' | trans }}</span>
            <div class="uk-form-controls uk-form-controls-text">
                <label><input type="checkbox" value="center-content" v-model="node.data.title"> {{ 'Show Title' | trans }}</label>
            </div>
        </div>
        <div class="uk-form-row">
            <span class="uk-form-label">{{ 'Menu' | trans }}</span>
            <div class="uk-form-controls uk-form-controls-text">
                <label><input type="checkbox" value="center-content" v-model="node.data.menu_hide"> {{ 'Hide in menu' | trans }}</label>
            </div>
        </div>
        <div class="uk-form-row">
            <label for="form-class" class="uk-form-label">{{ 'Class' | trans }}</label>
            <div class="uk-form-controls">
                <input id="form-class" class="uk-form-width-large" type="text" v-model="node.data.class">
            </div>
        </div>

    </div>

</template>

<script>

    module.exports = {

        section: {
            name: 'appearance',
            label: 'Appearance',
            priority: 90
        },

        props: ['node']

    }

</script>
