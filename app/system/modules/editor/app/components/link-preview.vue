<template>

    <a class="{{ link.class }}" href="{{ link.link }}" v-on="click: config">{{{ link.txt ? link.txt : 'Select Link' | trans }}} <span class="uk-icon-pencil"></span></a>

</template>

<script>

    module.exports = Vue.extend({

        props: ['index'],

        computed: {

            link: function() {
                return this.$parent.links[this.index] || {};
            }

        },

        methods: {

            config: function(e) {
                e.preventDefault();
                this.$parent.openModal(this.link);
            }

        }

    });

</script>
