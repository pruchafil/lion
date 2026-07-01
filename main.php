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

$app->get('/', [ParcelController::class, 'index']);

$app->run();