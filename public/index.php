<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {
    /**
     * The FactoryDefault Dependency Injector automatically registers
     * the services that provide a full stack framework.
     */
    $di = new FactoryDefault();

    $di->setShared(
        'session',
        function () {
            $session = new Session();

            $session->start();

            return $session;
        }
    );

    $di->set('dispatcher', function() {

        $eventsManager = new \Phalcon\Events\Manager();

        $eventsManager->attach("dispatch:beforeException", function($event, $dispatcher, $exception) {

            //Handle 404 exceptions
            if ($exception instanceof \Phalcon\Mvc\Dispatcher\Exception) {
                $dispatcher->forward(array(
                    'controller' => 'index',
                    'action' => 'show404'
                ));
                return false;
            }

            //Handle other exceptions
            $dispatcher->forward(array(
                'controller' => 'index',
                'action' => 'show503'
            ));

            return false;
        });

        $dispatcher = new \Phalcon\Mvc\Dispatcher();

        //Bind the EventsManager to the dispatcher
        $dispatcher->setEventsManager($eventsManager);

        return $dispatcher;

    }, true);

    /**
     * Read services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Handle routes
     */
    include APP_PATH . '/config/router.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Handle the request
     */
    $application = new \Phalcon\Mvc\Application($di);

    echo $application->handle($_SERVER['REQUEST_URI'])->getContent();
} catch (\Exception $e) {
    echo $e->getMessage() . '<br>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
}

