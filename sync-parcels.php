<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use App\Service\ParcelSyncService;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$containerFactory = require __DIR__ . '/config/Container.php';
$container = $containerFactory();

$container->get(ParcelSyncService::class)->syncJicinDistrict();