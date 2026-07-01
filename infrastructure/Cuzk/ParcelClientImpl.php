<?php

declare(strict_types=1);

namespace Infrastructure\Cuzk;

final readonly class ParcelClientImpl implements ParcelClient {
    private const string URL = "https://services.cuzk.cz/wfs/inspire-cp-wfs.asp";

    public function getParcelInBbox(float $minX, float $maxX, float $minY, float $maxY): string {
        $query = http_build_query([
            'service' => 'WFS',
            'version' => '2.0.0',
            'request' => 'GetFeature',
            'typeNames' => 'CP:CadastralParcel',
            'srsName' => 'EPSG:5514',
            'bbox' => "{$minX},{$minY},{$maxX},{$maxY},EPSG:5514",
        ]);

        $result = file_get_contents(self::URL . '?' . $query);

        if ($result === false) {
            throw new \RuntimeException('Failed to download parcels from CUZK.');
        }

        return $result;
    }
}