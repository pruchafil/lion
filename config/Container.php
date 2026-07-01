<?php

declare(strict_types=1);

namespace Infrastructure\Container;

use App\Controller\ParcelController;
use App\Repository\ParcelRepository;
use App\Repository\ParcelRepositoryImpl;
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
            $c->get(ParcelRepository::class)
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

    return $container;
};