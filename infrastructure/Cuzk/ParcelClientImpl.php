<?php

declare(strict_types=1);

namespace Infrastructure\Cuzk;

use DateInterval;
use DateInvalidOperationException;
use DateTime;
use RuntimeException;

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
            throw new RuntimeException('Failed to download parcels from CUZK.');
        }

        return $result;
    }

    /**
     * @throws DateInvalidOperationException
     */
    public function getParcelUpdateInBbox(float $minX, float $maxX, float $minY, float $maxY): string {
        $range = '<gml:Envelope srsName="http://www.opengis.net/def/crs/EPSG/0/5514" xmlns:gml="http://www.opengis.net/gml/3.2">
    <gml:lowerCorner>' . $minX . ' ' . $minY . '</gml:lowerCorner>
    <gml:upperCorner>' . $maxX . ' ' . $maxY . '</gml:upperCorner>
</gml:Envelope>';

        $now = new DateTime();
        $lastTimeSync = $now->sub(DateInterval::createFromDateString('30 day'));

        $query =  http_build_query([
                'service' => 'WFS',
                'version' => '2.0.0',
                'request' => 'GetFeature',
                'storedQuery_id' => 'GetChangedZonings',
                'DATE_OF_LAST_PUB' =>  $lastTimeSync->format('Y-m-d'),
                'RANGE' => $range,
            ]);

        $result = file_get_contents(self::URL . '?' . $query);

        if ($result === false) {
            throw new RuntimeException('Failed to download parcels from CUZK.');
        }

        return $result;
    }

    public function getParcelById(string $glmId): string {
        $query =  http_build_query([
            'service' => 'WFS',
            'version' => '2.0.0',
            'request' => 'GetFeature',
            'storedQuery_id' => 'GetFeatureById',
            'ID' =>  $glmId
        ]);

        $result = file_get_contents(self::URL . '?' . $query);

        if ($result === false) {
            throw new RuntimeException('Failed to download parcel from CUZK.');
        }

        return $result;
    }
}