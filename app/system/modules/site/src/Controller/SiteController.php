<?php

namespace Pagekit\Site\Controller;

use Pagekit\Application as App;
use Pagekit\Kernel\Exception\NotFoundException;
use Pagekit\Site\Model\Node;
use Pagekit\User\Model\Role;

/**
 * @Access("site: manage site")
 */
class SiteController
{
    /**
     * @Route("site")
     * @Access(admin=true)
     */
    public function indexAction()
    {
        $site = App::module('system/site');

        Node::fixOrphanedNodes();

        return [
            '$view' => [
                'title' => __('Pages'),
                'name'  => 'system/site/admin/index.php'
            ],
            '$data' => [
                'theme' => App::theme(),
                'types' => array_values($site->getTypes())
            ]
        ];
    }

    /**
     * @Route("site/edit")
     * @Access(admin=true)
     * @Request({"id", "menu"})
     */
    public function editAction($id = '', $menu = '')
    {
        $site = App::module('system/site');

        if (is_numeric($id)) {
            $node = Node::find($id);
        } else {
            $node = Node::create();
            $node->setType($id);

            if ($menu && !App::menu($menu)) {
                throw new NotFoundException(__('Menu not found.'));
            }

            $node->setMenu($menu);
        }

        if (!$node) {
            throw new NotFoundException(__('Node not found.'));
        }

        if (!$type = $site->getType($node->getType())) {
            throw new NotFoundException(__('Type not found.'));
        }

        return [
            '$view' => [
                'title' => __('Pages'),
                'name'  => 'system/site/admin/edit.php'
            ],
            '$data' => [
                'node' => $node,
                'type' => $type,
                'roles' => array_values(Role::findAll())

            ]
        ];
    }

    /**
     * @Route("site/settings")
     * @Access(admin=true)
     */
    public function settingsAction()
    {
        return [
            '$view' => [
                'title' => __('Settings'),
                'name'  => 'system/site/admin/settings.php'
            ],
            '$data' => [
                'config' => App::module('system/site')->config(['title', 'description', 'maintenance.', 'icons.', 'code.'])
            ]
        ];
    }

    /**
     * @Route("api/site/link", name="api/link")
     * @Request({"link"})
     */
    public function apiSiteAction($link)
    {
        return ['message' => 'success', 'url' => App::url($link, [], 'base') ?: $link];
    }
}
