<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?= $view->render('head') ?>
        <?php $view->style('theme', 'theme:css/theme.css') ?>
        <?php $view->script('jquery') ?>
        <?php $view->script('uikit') ?>
        <?php $view->script('uikit-sticky') ?>
        <?php $view->script('theme', 'theme:js/theme.js') ?>
    </head>
    <body>

        <?php if ($view->position()->exists('logo') || $view->menu()->exists('primary')) : ?>
        <div class="tm-navbar" data-uk-sticky="{showup: true, animation: 'uk-animation-slide-top'}">
            <div class="uk-container uk-container-center">

                <nav class="uk-navbar">

                    <?php if ($view->position()->exists('logo')) : ?>
                    <a class="uk-navbar-brand uk-hidden-small" href="<?= $view->url()->get() ?>">
                        <?= $view->position('logo') ?>
                    </a>
                    <?php endif ?>

                    <?php if ($view->menu()->exists('primary')) : ?>
                    <div class="uk-navbar-flip uk-hidden-small">
                        <?= $view->menu('primary', 'menu-navbar.php') ?>
                    </div>
                    <?php endif ?>

                    <?php if ($view->position()->exists('offcanvas') || $view->menu()->exists('offcanvas')) : ?>
                    <a href="#offcanvas" class="uk-navbar-toggle uk-visible-small" data-uk-offcanvas></a>
                    <?php endif ?>

                    <?php if ($view->position()->exists('logo')) : ?>
                    <a class="uk-navbar-brand uk-navbar-center uk-visible-small" href="<?= $view->url()->get() ?>">
                        <?= $view->position('logo') ?>
                    </a>
                    <?php endif ?>

                </nav>

            </div>
        </div>
        <?php endif ?>

        <?php if ($view->position()->exists('hero')) : ?>
        <div id="tm-hero" class="tm-hero uk-block uk-cover-background tm-block-height uk-flex uk-flex-middle" style="background-image: url('<?= $theme->config['hero-image']; ?>');">
            <div class="uk-container uk-container-center">

                <section class="uk-grid uk-grid-match" data-uk-grid-margin>
                    <?= $view->position('hero', 'position-grid.php') ?>
                </section>

            </div>
        </div>
        <?php endif; ?>

        <?php if ($view->position()->exists('top')) : ?>
        <div id="tm-top" class="tm-top uk-block uk-block-muted">
            <div class="uk-container uk-container-center">

                <section class="uk-grid uk-grid-match" data-uk-grid-margin>
                    <?= $view->position('top', 'position-grid.php') ?>
                </section>

            </div>
        </div>
        <?php endif; ?>

        <div id="tm-main" class="tm-main uk-block uk-block-default">
            <div class="uk-container uk-container-center">

                <div class="uk-grid" data-uk-grid-match data-uk-grid-margin>

                    <main class="<?= $view->position()->exists('sidebar') ? 'uk-width-medium-3-4' : 'uk-width-1-1'; ?>">
                        <?= $view->render('messages') ?>
                        <?= $view->render('content') ?>
                    </main>

                    <?php if ($view->position()->exists('sidebar')) : ?>
                    <aside class="uk-width-medium-1-4 <?= $theme->config['sidebar-first'] ? 'uk-flex-order-first-medium' : ''; ?>">
                        <?= $view->position('sidebar', 'position-panel.php') ?>
                    </aside>
                    <?php endif ?>

                </div>

            </div>
        </div>

        <?php if ($view->position()->exists('bottom')) : ?>
        <div id="tm-bottom" class="tm-bottom uk-block uk-block-muted">
            <div class="uk-container uk-container-center">

                <section class="uk-grid uk-grid-match" data-uk-grid-margin>
                    <?= $view->position('bottom', 'position-grid.php') ?>
                </section>

            </div>
        </div>
        <?php endif; ?>

        <?php if ($view->position()->exists('footer')) : ?>
        <div id="tm-footer" class="tm-footer uk-block uk-block-secondary uk-contrast">
            <div class="uk-container uk-container-center uk-text-center">

                <section class="uk-grid uk-grid-match" data-uk-grid-margin>
                    <?= $view->position('footer', 'position-grid.php') ?>
                </section>

            </div>
        </div>
        <?php endif; ?>

        <?php if ($view->position()->exists('offcanvas') || $view->menu()->exists('offcanvas')) : ?>
        <div id="offcanvas" class="uk-offcanvas">
            <div class="uk-offcanvas-bar">

                <?php if ($view->menu()->exists('offcanvas')) : ?>
                    <?= $view->menu('offcanvas', ['classes' => 'uk-nav-offcanvas']) ?>
                <?php endif ?>

                <?php if ($view->position()->exists('offcanvas')) : ?>
                    <?= $view->position('offcanvas', 'position-panel.php') ?>
                <?php endif ?>

            </div>
        </div>
        <?php endif ?>

        <?= $view->render('footer') ?>

    </body>
</html>
