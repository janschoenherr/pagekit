module.exports = {

    ready: function () {

        var $this = this, $el = $(this.$el).wrap('<div class="pk-editor"></div>');

        this.editor = CodeMirror.fromTextArea(this.$el, _.extend({
            mode: "htmlmixed",
            dragDrop: false,
            autoCloseTags: true,
            matchTags: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            indentUnit: 4,
            indentWithTabs: false,
            tabSize: 4
        }, this.options));

        $el.attr('data-uk-check-display', 'true').on('display.uk.check', function(e) {
            $this.editor.refresh();
        });

        this.editor.on('change', function() {
            $this.editor.save();
            $el.trigger('input');
        });
    },

    watch: {

        value: function (value) {
            if (value != this.editor.getValue()) {
                this.editor.setValue(value);
            }
        }
    }
};
