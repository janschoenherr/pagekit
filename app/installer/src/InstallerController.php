<?php

namespace Pagekit\Installer;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\ConnectionException;
use Pagekit\Application as App;
use Pagekit\Config\Config;
use Pagekit\Util\Arr;

class InstallerController
{
    /**
     * @var bool
     */
    protected $config;

    /**
     * @var string
     */
    protected $configFile = 'config.php';

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->config = file_exists($this->configFile);
    }

    public function indexAction()
    {
        return [
            '$view' => [
                'title' => __('Pagekit Installer'),
                'name' => 'app/installer/views/install.php'
            ]
        ];
    }

    /**
     * @Request({"config": "array"})
     */
    public function checkAction($config = [])
    {
        $status  = 'no-connection';
        $message = '';

        try {

            try {

                if (!$this->config) {
                    foreach ($config as $name => $values) {
                        if ($module = App::module($name)) {
                            $module->config = Arr::merge($module->config, $values);
                        }
                    }
                }

                App::db()->connect();

                if (App::db()->getUtility()->tableExists('@system_config')) {
                    $status  = 'tables-exist';
                    $message = __('Existing Pagekit installation detected. Choose different table prefix?');
                } else {
                    $status = 'no-tables';
                }

            } catch (ConnectionException $e) {

                if ($e->getPrevious()->getCode() == 1049) {
                    $this->createDatabase();
                    $status = 'no-tables';
                } else {
                    throw $e;
                }
            }

        } catch (\Exception $e) {

            $message = __('Database connection failed!');

            if ($e->getCode() == 1045) {
                $message = __('Database access denied!');
            }
        }

        return ['status' => $status, 'message' => $message];
    }

    /**
     * @Request({"config": "array", "option": "array", "user": "array"})
     */
    public function installAction($config = [], $option = [], $user = [])
    {
        $status  = $this->checkAction($config, false);
        $message = $status['message'];
        $status  = $status['status'];

        try {

            if ('no-connection' == $status) {
                App::abort(400, __('No database connection.'));
            }

            if ('tables-exist' == $status) {
                App::abort(400, $message);
            }

            if ($version = App::migrator()->create('app/system/migrations')->run()) {
                App::config()->set('system', compact('version'));
            }

            App::db()->insert('@system_user', [
                'name' => $user['username'],
                'username' => $user['username'],
                'password' => App::get('auth.password')->hash($user['password']),
                'status' => 1,
                'email' => $user['email'],
                'registered' => new \DateTime,
                'roles' => [2, 3]
            ], ['string', 'string', 'string', 'string', 'string', 'datetime', 'simple_array']);

            if ($sampleData = App::module('installer')->config('sampleData')) {

                $sql = file_get_contents($sampleData);

                foreach (explode(';', $sql) as $query) {
                    if ($query = trim($query)) {
                        App::db()->executeUpdate($query);
                    }
                }
            }

            if (!$this->config) {

                $configuration = new Config();

                foreach ($config as $key => $value) {
                    $configuration->set($key, $value);
                }

                $configuration->set('system.key', App::get('auth.random')->generateString(64));

                if (!file_put_contents($this->configFile, $configuration->dump())) {

                    $status = 'write-failed';

                    App::abort(400, __('Can\'t write config.'));
                }
            }

            foreach ($option as $name => $values) {
                App::config()->set($name, $values);
            }

            App::module('system/cache')->clearCache();

            $status = 'success';

        } catch (DBALException $e) {

            $status  = 'db-sql-failed';
            $message = __('Database error: %error%', ['%error%' => $e->getMessage()]);

        } catch (\Exception $e) {

            $message = $e->getMessage();

        }

        return ['status' => $status, 'message' => $message];
    }

    /**
     * @return void
     */
    protected function createDatabase()
    {
        $module = App::module('database');
        $params = $module->config('connections')[$module->config('default')];

        $name = $params['dbname'];
        unset($params['dbname']);

        $db = DriverManager::getConnection($params);
        $db->getSchemaManager()->createDatabase($db->quoteIdentifier($name));
        $db->close();
    }
}
