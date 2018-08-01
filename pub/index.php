<?php
/**
 * Copyright (c) 2018 Magenerds
 * All rights reserved
 *
 * This product includes proprietary software developed at Magenerds, Germany
 * For more information see http://www.magenerds.com/
 *
 * To obtain a valid license for using this software please contact us at
 * info@magenerds.com
 */

use Doctrine\DBAL\DriverManager;
use MomMock\Controller\Backend\OrderController;
use MomMock\Controller\Backend\ShipmentController;
use MomMock\Controller\TokenController;
use MomMock\Controller\MomController;
use MomMock\Helper\MethodResolver;
use MomMock\Helper\TemplateHelper;
use MomMock\Helper\RpcClient;

require '../vendor/autoload.php';

$app = new \Slim\App;

$container = $app->getContainer();

$container['db'] = function($c) {
    return DriverManager::getConnection([
        'dbname' => 'mom',
        'user' => 'root',
        'password' => 'root',
        'host' => 'localhost',
        'driver' => 'pdo_mysql',
    ]);
};

$container['templ'] = function($c) {
    return new Twig_Environment(new Twig_Loader_Filesystem('../app/templates'));
};

$container['method_resolver'] = function($c) {
    return new MethodResolver();
};

$container['template_helper'] = function($c) {
    return new TemplateHelper();
};

$container['rpc_client'] = function($c) {
    return new RpcClient($c->get('db'));
};

$app->get('/', OrderController::class . ':listAction');
$app->get('/order/{id}', OrderController::class . ':detailAction');
$app->get('/shipment/create', ShipmentController::class . ':createShipmentAction');
$app->post('/', MomController::class . ':indexAction');
$app->post('/delegate/oms', MomController::class . ':indexAction');
$app->post('/oauth/token', TokenController::class . ':indexAction');

$app->run();