<template>

    <div class="uk-form-horizontal">

        <partial name="settings"></partial>

    </div>

</template>

<script>

    module.exports = {

        inherit: true,

        section: {
            label: 'Settings',
            priority: 0,
            active: '^blog$'
        },

        template: __vue_template__

    };

    window.Site.component('blog', module.exports);

</script>
