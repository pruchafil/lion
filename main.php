<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use App\Controller\ParcelController;
use Dotenv\Dotenv;
use Slim\Factory\AppFactory;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$containerFactory = require __DIR__ . '/config/Container.php';
$container = $containerFactory();

AppFactory::setContainer($container);

$app = AppFactory::create();

$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, true, true);

$app->add(function ($request, $handler) {
    $response = $handler->handle($request);

    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:3000')
        ->withHeader('Access-Control-Allow-Headers', 'Content-Type')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
});

$app->options('/{routes:.+}', function ($request, $response) {
    return $response;
});

$app->get('/parcel/{id}', [ParcelController::class, 'getById']);
$app->get('/parcel', [ParcelController::class, 'getArea']);

$app->run();