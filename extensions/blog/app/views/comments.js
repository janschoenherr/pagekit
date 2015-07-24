module.exports = {

    data: function () {
        return _.extend({
            post: {},
            tree: {},
            comments: [],
            count: 0,
            reply: 0
        }, window.$comments);
    },

    created: function () {
        this.Comments = this.$resource('api/blog/comment/:id');
        this.load();
    },

    methods: {

        load: function () {

            this.Comments.query({post: this.config.post}, function (data) {
                this.$set('comments', data.comments);
                this.$set('tree', _.groupBy(data.comments, 'parent_id'));
                this.$set('post', data.posts[0]);
                this.$set('count', data.count);
                this.$set('reply', 0);
            });
        }

    },

    components: {

        'comments-item': {

            inherit: true,
            props: ['depth'],
            template: '#comments-item',

            computed: {

                showReply: function () {

                    return this.config.enabled && this.reply && this.reply == this.comment.id;

                },

                showReplyButton: function () {

                    return this.config.enabled && this.depth < this.config.max_depth && !this.showReply;

                },

                remainder: function () {

                    return this.depth >= this.config.max_depth && this.tree[this.comment.id] || [];

                },

                permalink: function () {

                    return this.post.url + '#' + this.comment.id;

                }

            },

            methods: {

                replyTo: function (e) {
                    e.preventDefault();
                    this.$set('reply', this.comment.id);
                }

            }

        },

        'comments-reply': {

            inherit: true,
            template: '#comments-reply',

            data: function () {
                return {
                    author: '',
                    email: '',
                    content: ''
                };
            },

            methods: {

                cancel: function (e) {
                    e.preventDefault();
                    this.$set('reply', 0);
                    this.$set('author', '');
                    this.$set('email', '');
                    this.$set('content', '');
                    this.$set('replyForm', {});
                },

                save: function (e) {
                    e.preventDefault();

                    var comment = {
                        parent_id: this.comment ? this.comment.id : 0,
                        post_id: this.config.post,
                        content: this.content
                    };

                    if (!this.user.isAuthenticated) {
                        comment['author'] = this.author;
                        comment['email'] = this.email;
                    }

                    // TODO handle errors
                    this.$resource('api/blog/comment/:id').save({id: 0}, {comment: comment}, function (data) {
                        this.cancel(e);
                        this.load();

                        UIkit.notify(this.$trans('Thanks for commenting.'));
                    });
                }

            },

            validators: {

                name: function (value) {
                    return !this.config.requireinfo || Vue.validators['required'](value);
                },

                email: function (value) {
                    return !this.config.requireinfo && !Vue.validators['required'](value) || Vue.validators['email'](value);
                }

            }

        }

    }

};

jQuery(function () {
    new Vue(module.exports).$mount('#comments');
});

