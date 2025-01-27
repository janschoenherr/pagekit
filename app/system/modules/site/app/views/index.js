module.exports = {

    data: function () {
        return _.merge({
            edit: undefined,
            menu: undefined,
            menus: [],
            nodes: [],
            tree: [],
            selected: []
        }, window.$data);
    },

    created: function () {
        this.Menus = this.$resource('api/site/menu/:id');
        this.Nodes = this.$resource('api/site/node/:id');
        this.load();
    },

    methods: {

        load: function () {
            return this.Menus.query(function (data) {
                this.$set('menus', data);
            });
        },

        isActive: function (menu) {
            return this.menu && this.menu.id === menu.id;
        },

        selectMenu: function (menu, reload) {

            var vm = this;

            if (reload === false) {
                this.$set('menu', menu);
            } else {

                this.load().then(function(){
                    vm.$set('menu', menu);
                });
            }
        },

        removeMenu: function (menu) {
            this.Menus.delete({id: menu.id}, this.load);
        },

        editMenu: function (menu) {

            if (!menu) {
                menu = {
                    id: '',
                    label: ''
                };
            }

            this.$set('edit', _.extend({
                assigned: _.pluck(_.filter(this.theme.menus, 'assigned', menu.id), 'name')
            }, menu));

            this.$.modal.open();
        },

        saveMenu: function (menu) {

            this.Menus.save({id: menu.id}, {menu: menu}, function (data) {
                this.load();
                this.$set('theme', data.theme);
            }).error(function (msg) {
                UIkit.notify(msg, 'danger');
            });

            this.cancel();
        },

        cancel: function () {
            this.$.modal.close();
        },

        setFrontpage: function (node) {
            this.Nodes.save({id: 'frontpage'}, {id: node.id}, function () {
                this.load();
                UIkit.notify('Frontpage updated.');
            });
        },

        status: function (status) {

            var nodes = this.getSelected();

            nodes.forEach(function (node) {
                node.status = status;
            });

            this.Nodes.save({id: 'bulk'}, {nodes: nodes}, function () {
                this.load();
                UIkit.notify('Page(s) saved.');
            });
        },

        toggleStatus: function (node) {

            node.status = node.status === 1 ? 0 : 1;

            this.Nodes.save({id: node.id}, {node: node}, function () {
                this.load();
                UIkit.notify('Page saved.');
            });
        },

        moveNodes: function (menu) {

            var nodes = this.getSelected();

            nodes.forEach(function (node) {
                node.menu = menu;
            });

            this.Nodes.save({id: 'bulk'}, {nodes: nodes}, function () {
                this.load();
                UIkit.notify(this.$trans('Pages moved to %menu%.', {menu: _.find(this.menus.concat({label: this.$trans('Trash')}), 'id', menu).label}));
            });
        },

        removeNodes: function () {

            if (this.menu.id !== 'trash') {

                var nodes = this.getSelected();

                nodes.forEach(function (node) {
                    node.status = 0;
                });

                this.moveNodes('trash');

            } else {
                this.Nodes.delete({id: 'bulk'}, {ids: this.selected}, function () {
                    this.load();
                    UIkit.notify('Page(s) deleted.');
                });
            }
        },

        getType: function (node) {
            return _.find(this.types, 'id', node.type);
        },

        getSelected: function () {
            return this.nodes.filter(function (node) {
                return this.isSelected(node);
            }, this);
        },

        isSelected: function (node, children) {

            if (_.isArray(node)) {
                return _.every(node, function (node) {
                    return this.isSelected(node, children);
                }, this);
            }

            return this.selected.indexOf(node.id.toString()) !== -1 && (!children || !this.tree[node.id] || this.isSelected(this.tree[node.id], true));
        },

        toggleSelect: function(node) {

            var index = this.selected.indexOf(node.id.toString());

            if (index == -1) {
                this.selected.push(node.id.toString());
            } else {
                this.selected.splice(index, 1);
            }
        }

    },

    computed: {

        showDelete: function () {
            return this.showMove && _.every(this.getSelected(), function (node) {
                    return !(this.getType(node) || {})['protected'];
                }, this);
        },

        showMove: function () {
            return this.isSelected(this.getSelected(), true);
        }

    },

    watch: {

        menu: function () {

            this.$set('selected', []);

            return this.Nodes.query({menu: this.$get('menu.id')}, function (nodes) {
                this.$set('nodes', nodes);
                this.$set('tree', _(nodes).sortBy('priority').groupBy('parentId').value());
            });
        },

        menus: function (menus) {
            this.selectMenu(_.find(menus, 'id', this.$get('menu.id')) || menus[0], false);
        },

        nodes: function () {

            var vm = this;

            // TODO this is still buggy
            UIkit.nestable(this.$$.nestable, {maxDepth: 20, group: 'site.nodes'}).off('change.uk.nestable').on('change.uk.nestable', function (e, nestable, el, type) {

                if (type && type !== 'removed') {

                    vm.Nodes.save({id: 'updateOrder'}, {menu: vm.menu.id, nodes: nestable.list()}, function () {

                        // @TODO reload everything on reorder really needed?
                        vm.load().success(function () {

                            // hack for weird flickr bug
                            if (el.parent()[0] === nestable.element[0]) {
                                setTimeout(function() {
                                    el.remove();
                                }, 50);
                            }
                        });

                    }).error(function() {
                        UIkit.notify(this.$trans('Reorder failed.'), 'danger');
                    });
                }
            });
        }

    },

    filters: {

        label: function (id) {
            return _.result(_.find(this.menus, 'id', id), 'label');
        },

        protected: function (types) {
            return _.reject(types, 'protected', true);
        },

        trash: function (menus) {
            return _.reject(menus, 'id', 'trash');
        }

    },

    components: {

        node: {

            inherit: true,
            template: '#node',

            computed: {

                isFrontpage: function () {
                    return this.node.url === '/';
                },

                type: function() {
                    return this.getType(this.node);
                }

            }
        }

    }

};

$(function () {

    (new Vue(module.exports)).$mount('#site');

});
