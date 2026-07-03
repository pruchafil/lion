<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Parcel;
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

        $changedZonings = $parser->parseZoning($update);

        foreach ($changedZonings as $zoning) {
            foreach ($this->grid($zoning->minX, $zoning->minY, $zoning->maxX, $zoning->maxY) as $tile) {
                $xml = $this->client->getParcelInBbox(
                    $tile['minX'],
                    $tile['maxX'],
                    $tile['minY'],
                    $tile['maxY']
                );

                $parcels = $parser->parseParcels($xml);

                $currentGmlIds = array_map(
                    static fn(Parcel $parcel) => $parcel->gmlId,
                    $parcels
                );

                $this->repository->deleteMissingInBbox(
                    $zoning->code,
                    $tile['minX'],
                    $tile['minY'],
                    $tile['maxX'],
                    $tile['maxY'],
                    $currentGmlIds
                );

                foreach ($parcels as $parcel) {
                    $this->repository->upsertParcel($parcel);
                }
            }
        }
    }

    private function grid(
        float $minX,
        float $minY,
        float $maxX,
        float $maxY
    ): array {
        $tiles = [];

        for ($x = $minX; $x < $maxX; $x = 500 + 500) {
            for ($y = $minY; $y < $maxY; $y = 500 + 500) {
                $tiles[] = [
                    'minX' => $x,
                    'minY' => $y,
                    'maxX' => min($x + 500, $maxX),
                    'maxY' => min($y + 500, $maxY),
                ];
            }
        }

        return $tiles;
    }
}