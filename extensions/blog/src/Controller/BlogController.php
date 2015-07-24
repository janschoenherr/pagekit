<?php

namespace Pagekit\Blog\Controller;

use Pagekit\Application as App;
use Pagekit\Blog\Model\Comment;
use Pagekit\Blog\Model\Post;
use Pagekit\User\Model\Role;

/**
 * @Access(admin=true)
 */
class BlogController
{
    /**
     * @Access("blog: manage own posts || blog: manage all posts")
     * @Request({"filter": "array", "page":"int"})
     */
    public function postAction($filter = null, $page = 0)
    {
        return [
            '$view' => [
                'title' => __('Posts'),
                'name'  => 'blog:views/admin/post-index.php'
            ],
            '$data' => [
                'statuses' => Post::getStatuses(),
                'authors'  => Post::getAuthors(),
                'canEditAll' => App::user()->hasAccess('blog: manage all posts'),
                'config'   => [
                    'filter' => $filter,
                    'page'   => $page
                ]
            ]
        ];
    }

    /**
     * @Route("/post/edit", name="post/edit")
     * @Access("blog: manage own posts || blog: manage all posts")
     * @Request({"id": "int"})
     */
    public function editAction($id = 0)
    {
        try {

            if (!$post = Post::where(compact('id'))->related('user')->first()) {

                if ($id) {
                    App::abort(404, __('Invalid post id.'));
                }

                $module = App::module('blog');

                $post = Post::create();
                $post->setUser(App::user());
                $post->setStatus(Post::STATUS_DRAFT);
                $post->setDate(new \DateTime);
                $post->setUser(App::user());
                $post->setCommentStatus((bool) $module->config('posts.comments_enabled'));
                $post->set('title', $module->config('posts.show_title'));
                $post->set('markdown', $module->config('posts.markdown_enabled'));
            }

            $user = App::user();
            if(!$user->hasAccess('blog: manage all posts') && $post->getUserId() !== $user->getId()) {
                App::abort(403, __('Insufficient User Rights.'));
            }

            $roles = App::db()->createQueryBuilder()
                ->from('@system_role')
                ->where(['id' => Role::ROLE_ADMINISTRATOR])
                ->orWhere('permissions REGEXP '.App::db()->quote('(^|,)(blog: manage all posts|blog: manage own posts)($|,)'))
                ->execute('id')
                ->fetchAll(\PDO::FETCH_COLUMN);

            $authors = App::db()->createQueryBuilder()
                ->from('@system_user')
                ->where('roles REGEXP '.App::db()->quote('(^|,)('.implode('|', $roles).')($|,)'))
                ->execute('id, username')
                ->fetchAll();

            return [
                '$view' => [
                    'title' => $id ? __('Edit Post') : __('Add Post'),
                    'name'  => 'blog:views/admin/post-edit.php'
                ],
                '$data' => [
                    'post'     => $post,
                    'statuses' => Post::getStatuses(),
                    'roles'    => array_values(Role::findAll()),
                    'canEditAll' => $user->hasAccess('blog: manage all posts'),
                    'authors'  => $authors
                ],
                'post' => $post
            ];

        } catch (\Exception $e) {

            App::message()->error($e->getMessage());

            return App::redirect('@blog/post');
        }
    }

    /**
     * @Access("blog: manage comments")
     * @Request({"filter": "array", "post":"int", "page":"int"})
     */
    public function commentAction($filter = [], $post = 0, $page = 0)
    {
        $post = Post::find($post);

        return [
            '$view' => [
                'title' => $post ? __('Comments on %title%', ['%title%' => $post->getTitle()]) : __('Comments'),
                'name'  => 'blog:views/admin/comment-index.php'
            ],
            '$data'   => [
                'statuses' => Comment::getStatuses(),
                'config'   => [
                    'filter' => $filter,
                    'page'   => $page,
                    'post'   => $post,
                    'limit'  => App::module('blog')->config('comments.comments_per_page')
                ]
            ]
        ];
    }

    /**
     * @Access("blog: manage settings")
     */
    public function settingsAction()
    {
        return [
            '$view' => [
                'title' => __('Blog Settings'),
                'name'  => 'blog:views/admin/settings.php'
            ],
            '$data' => [
                'config' => App::module('blog')->config()
            ]
        ];
    }
}
