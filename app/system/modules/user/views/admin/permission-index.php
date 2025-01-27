<?php $view->script('permission-index', 'system/user:app/bundle/permission-index.js', 'vue') ?>

<div id="permissions" class="uk-form" v-cloak>

    <h2>{{ 'Permissions' | trans }}</h2>

    <div id="{{ $key }}" class="uk-overflow-container uk-margin-large" v-repeat="group: permissions">
        <table class="uk-table uk-table-hover uk-table-middle">
            <thead>
                <tr>
                    <th class="pk-table-min-width-200">{{ $key }}</th>
                    <th class="pk-table-width-minimum"></th>
                    <th class="pk-table-width-minimum pk-table-max-width-100 uk-text-truncate uk-text-center" v-repeat="roles">{{ name }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-repeat="permission: group" v-class="uk-visible-hover-inline: permission.trusted">
                    <td class="pk-table-text-break">
                        <span title="{{ permission.description | trans }}" data-uk-tooltip="{pos:'top-left'}">{{ permission.title | trans }}</span>
                    </td>
                    <td>
                        <i class="pk-icon-warning uk-invisible" title="{{ 'This permission has security implications. Give it trusted roles only.' | trans }}" data-uk-tooltip v-if="permission.trusted"></i>
                    </td>
                    <td class="uk-text-center" v-repeat="role: roles">

                        <span class="uk-position-relative" v-show="showFakeCheckbox(role, $parent.$key)">
                            <input type="checkbox" checked disabled>
                            <span class="uk-position-cover" v-if="!role.isAdministrator" v-on="click: addPermission(role, $parent.$key), click: savePermissions(role)"></span>
                        </span>

                        <input type="checkbox" value="{{ $parent.$key }}" v-show="!showFakeCheckbox(role, $parent.$key)" v-checkbox="role.permissions" v-on="click: savePermissions(role)">
                    </td>
                </tr>
            </tbody>
        </table>

    </div>

</div>
