<?php

declare(strict_types=1);

namespace Infrastructure\Container;

use App\Controller\ParcelController;
use App\Repository\CachedRepository;
use App\Repository\CachedRepositoryImpl;
use App\Repository\ParcelRepository;
use App\Repository\ParcelRepositoryImpl;
use App\Service\ParcelService;
use App\Service\ParcelServiceImpl;
use App\Service\ParcelSyncService;
use App\Service\ParcelSyncServiceImpl;
use DI\Container;
use Infrastructure\Cuzk\ParcelClient;
use Infrastructure\Cuzk\ParcelClientImpl;
use PDO;
use function Infrastructure\Database\getConnection;

return function (): Container {

    $container = new Container();

    $container->set(PDO::class, function (): PDO {
        return getConnection();
    });

    $container->set(ParcelRepository::class, function (Container $c): ParcelRepository {
        return new ParcelRepositoryImpl(
            $c->get(PDO::class)
        );
    });

    $container->set(ParcelController::class, function (Container $c): ParcelController {
        return new ParcelController(
            $c->get(ParcelService::class)
        );
    });

    $container->set(ParcelClient::class, function (Container $c): ParcelClient {
        return new ParcelClientImpl();
    });

    $container->set(ParcelSyncService::class, function (Container $c): ParcelSyncService {
        return new ParcelSyncServiceImpl(
            $c->get(ParcelClient::class),
            $c->get(ParcelRepository::class)
        );
    });

    $container->set(CachedRepository::class, function (Container $c): CachedRepository {
        return new CachedRepositoryImpl(
            $c->get(PDO::class)
        );
    });

    $container->set(ParcelService::class, function (Container $c): ParcelService {
        return new ParcelServiceImpl(
            $c->get(ParcelRepository::class),
            $c->get(ParcelSyncService::class),
            $c->get(CachedRepository::class)
        );
    });

    return $container;
};