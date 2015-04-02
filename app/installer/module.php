<?php

use Pagekit\System\Event\ResponseListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return [

    'name' => 'installer',

    'main' => function ($app) {

        if (!$this->config['enabled']) {
            return false;
        }

        $app->on('kernel.request', function ($event) use ($app) {
            if ($locale = $app['request']->getPreferredLanguage()) {
                $app['translator']->setLocale($locale);
            }
        });

        $app->error(function (NotFoundHttpException $e) use ($app) {
            return $app['response']->redirect('@installer/installer');
        });

        $app->subscribe(new ResponseListener());
    },

    'require' => [

        'application',
        'locale',
        'cache',
        'migration',
        'option'

    ],

    'autoload' => [

        'Pagekit\\System\\' => '../modules/system/src',
        'Pagekit\\Installer\\' => 'src'

    ],

    'controllers' => [

        '@installer: /installer' => 'Pagekit\\Installer\\InstallerController'

    ],

    'config' => [

        'enabled'    => false,
        'sampleData' => __DIR__.'/sample_data.sql'

    ]

];