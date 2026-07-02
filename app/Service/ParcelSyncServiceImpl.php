<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\ParcelRepository;
use Exception;
use Infrastructure\Cuzk\ParcelClient;
use Infrastructure\Cuzk\ParcelXmlParser;

readonly class ParcelSyncServiceImpl implements ParcelSyncService {

    function __construct(private ParcelClient $client, private ParcelRepository $repository)
    { }

    /**
     * @throws Exception
     */
    public function syncJicinDistrict(): void
    {
        $minX = -700000;
        $minY = -1032000;
        $maxX = -645000;
        $maxY = -1004000;
        $step = 5000;

        $parser = new ParcelXmlParser();

        for ($x = $minX; $x < $maxX; $x += $step) {
            for ($y = $minY; $y < $maxY; $y += $step) {
                $gml = $this->client->getParcelInBbox(
                    $x,
                    min($x + $step, $maxX),
                    $y,
                    min($y + $step, $maxY)
                );

                 foreach ($parser->parseParcels($gml) as $parcel) {
                     $this->repository->upsertParcel($parcel);
                 }

                echo "Downloaded tile {$x},{$y}\n";
            }
        }
    }

    /**
     * @throws Exception
     */
    public function updateGlobalCache(): void {
        $minX = -700000;
        $minY = -1032000;
        $maxX = -645000;
        $maxY = -1004000;

        $update = $this->client->getParcelUpdateInBbox($minX, $maxX, $minY, $maxY);
        $parser = new ParcelXmlParser();

        foreach ($parser->parseZoning($update) as $gmlId) {
            $this->repository->deleteParcelByGlmId($gmlId);

            $newParcelXml = $this->client->getParcelById($gmlId);
            $newParcel = $parser->parseParcel($newParcelXml);

            $this->repository->upsertParcel($newParcel);
        }
    }
}