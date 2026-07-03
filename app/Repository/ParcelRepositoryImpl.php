<?php

namespace App\Repository;

use App\Entity\Parcel;
use App\Entity\ParcelArea;
use DateMalformedStringException;
use PDO;

readonly class ParcelRepositoryImpl implements ParcelRepository {

    function __construct(private PDO $pdo) { }

    public function getParcelById(int $id): ?Parcel {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                cuzk_gml_id,
                national_cadastral_reference,
                ku_code,
                ku_name,
                parcel_number,
                area_m2,
                ST_AsEWKT(geom) AS geom_ewkt
            FROM parcel
            WHERE id = :id
        ");

        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return Parcel::fromDatabaseRow($row);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getParcelByGlmId(string $gmlId): ?Parcel {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                cuzk_gml_id,
                national_cadastral_reference,
                ku_code,
                ku_name,
                parcel_number,
                area_m2,
                ST_AsEWKT(geom) AS geom_ewkt
            FROM parcel
            WHERE cuzk_gml_id = :id
        ");

        $stmt->execute(['id' => $gmlId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return Parcel::fromDatabaseRow($row);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getParcelByCadastralUnitCode(string $cadastralUnitCode): ?Parcel {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                cuzk_gml_id,
                national_cadastral_reference,
                ku_code,
                ku_name,
                parcel_number,
                area_m2,
                ST_AsEWKT(geom) AS geom_ewkt
            FROM parcel
            WHERE ku_code = :code
        ");

        $stmt->execute(['code' => $cadastralUnitCode]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return Parcel::fromDatabaseRow($row);
    }

    /**
     * @throws DateMalformedStringException
     */
    public function getParcelByParcelNumber(string $parcelNumber): ?Parcel {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                cuzk_gml_id,
                national_cadastral_reference,
                ku_code,
                ku_name,
                parcel_number,
                area_m2,
                ST_AsEWKT(geom) AS geom_ewkt
            FROM parcel
            WHERE parcel_number = :parcel
        ");

        $stmt->execute(['parcel' => $parcelNumber]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return Parcel::fromDatabaseRow($row);
    }

    public function upsertParcel(Parcel $parcel): void {
        $stmt = $this->pdo->prepare("
        INSERT INTO parcel (
            cuzk_gml_id,
            national_cadastral_reference,
            ku_code,
            ku_name,
            parcel_number,
            area_m2,
            geom
        )
        VALUES (
            :gml_id,
            :national_cadastral_reference,
            :ku_code,
            :ku_name,
            :parcel_number,
            :area_m2,
            ST_GeomFromEWKT(:geom_ewkt)
        )
        ON CONFLICT (cuzk_gml_id)
        DO UPDATE SET
            national_cadastral_reference = EXCLUDED.national_cadastral_reference,
            ku_code = EXCLUDED.ku_code,
            ku_name = EXCLUDED.ku_name,
            parcel_number = EXCLUDED.parcel_number,
            area_m2 = EXCLUDED.area_m2,
            geom = EXCLUDED.geom
    ");

        $stmt->execute([
            'gml_id' => $parcel->gmlId,
            'national_cadastral_reference' => $parcel->nationalCadastralReference,
            'ku_code' => $parcel->cadastralUnitCode,
            'ku_name' => $parcel->cadastralUnitName,
            'parcel_number' => $parcel->parcelNumber,
            'area_m2' => $parcel->areaM2,
            'geom_ewkt' => $parcel->geomEwkt
        ]);
    }

    public function getArea(float $minX, float $minY, float $maxX, float $maxY): array {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                ST_AsGeoJSON(ST_Transform(geom, 4326)) AS geom_ewkt
            FROM parcel
            WHERE geom && ST_Transform(ST_MakeEnvelope($minX, $minY, $maxX, $maxY, 4326), 5514)
            LIMIT 200
        ");

        $stmt->execute();

        $areas = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $areas[] = ParcelArea::fromDatabaseRow($row);
        }

        return $areas;
    }

    public function deleteParcelByGlmId(string $gmlId): void {
        $stmt = $this->pdo->prepare("
            DELETE FROM parcel
            WHERE cuzk_gml_id = :gml_id
        ");

        $stmt->execute(['gml_id' => $gmlId]);
    }

    public function deleteMissingInBbox(
        string $kuCode,
        float $minX,
        float $minY,
        float $maxX,
        float $maxY,
        array $currentGmlIds
    ): void {
        if ($currentGmlIds === []) {
            return;
        }

        $placeholders = [];

        foreach ($currentGmlIds as $i => $_) {
            $placeholders[] = ':gml_' . $i;
        }

        $sql = "
            DELETE FROM parcel
            WHERE ku_code = :ku_code
            AND geom && ST_MakeEnvelope(:min_x, :min_y, :max_x, :max_y, 5514)
            AND cuzk_gml_id NOT IN (" . implode(',', $placeholders) . ")
        ";

        $params = [
            'ku_code' => $kuCode,
            'min_x' => $minX,
            'min_y' => $minY,
            'max_x' => $maxX,
            'max_y' => $maxY,
        ];

        foreach ($currentGmlIds as $i => $gmlId) {
            $params['gml_' . $i] = $gmlId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
    }
}