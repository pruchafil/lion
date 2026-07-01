<?php

namespace App\Repository;

use App\Entity\Parcel;
use DateTimeImmutable;
use JsonException;
use DateMalformedStringException;
use PDO;

readonly class ParcelRepositoryImpl implements ParcelRepository
{

    function __construct(private PDO $pdo)
    {
    }

    /**
     * @throws DateMalformedStringException
     * @throws JsonException
     */
    public function getParcelById(int $id): ?Parcel
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                cuzk_gml_id,
                national_cadastral_reference,
                ku_code,
                ku_name,
                parcel_number,
                area_m2,
                ST_AsEWKT(geom) AS geom_ewkt,
                raw_gml::text AS raw_data,
                cached_at
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
     * @throws JsonException
     */
    public function getParcelByGlmId(string $gmlId): ?Parcel
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                cuzk_gml_id,
                national_cadastral_reference,
                ku_code,
                ku_name,
                parcel_number,
                area_m2,
                ST_AsEWKT(geom) AS geom_ewkt,
                raw_gml::text AS raw_data,
                cached_at
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
     * @throws JsonException
     */
    public function getParcelByCadastralUnitCode(string $cadastralUnitCode): ?Parcel
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                cuzk_gml_id,
                national_cadastral_reference,
                ku_code,
                ku_name,
                parcel_number,
                area_m2,
                ST_AsEWKT(geom) AS geom_ewkt,
                raw_gml::text AS raw_data,
                cached_at
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
     * @throws JsonException
     */
    public function getParcelByParcelNumber(string $parcelNumber): ?Parcel
    {
        $stmt = $this->pdo->prepare("
            SELECT
                id,
                cuzk_gml_id,
                national_cadastral_reference,
                ku_code,
                ku_name,
                parcel_number,
                area_m2,
                ST_AsEWKT(geom) AS geom_ewkt,
                raw_gml::text AS raw_data,
                cached_at
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

    /**
     * @throws JsonException
     */
    public function upsertParcel(Parcel $parcel): void
    {
        $stmt = $this->pdo->prepare("
        INSERT INTO parcel (
            cuzk_gml_id,
            national_cadastral_reference,
            ku_code,
            ku_name,
            parcel_number,
            area_m2,
            geom,
            raw_gml,
            cached_at
        )
        VALUES (
            :gml_id,
            :national_cadastral_reference,
            :ku_code,
            :ku_name,
            :parcel_number,
            :area_m2,
            ST_GeomFromEWKT(:geom_ewkt),
            :raw_gml::jsonb,
            now()
        )
        ON CONFLICT (cuzk_gml_id)
        DO UPDATE SET
            national_cadastral_reference = EXCLUDED.national_cadastral_reference,
            ku_code = EXCLUDED.ku_code,
            ku_name = EXCLUDED.ku_name,
            parcel_number = EXCLUDED.parcel_number,
            area_m2 = EXCLUDED.area_m2,
            geom = EXCLUDED.geom,
            raw_gml = EXCLUDED.raw_gml,
            cached_at = now()
    ");

        $stmt->execute([
            'gml_id' => $parcel->gmlId,
            'national_cadastral_reference' => $parcel->nationalCadastralReference,
            'ku_code' => $parcel->cadastralUnitCode,
            'ku_name' => $parcel->cadastralUnitName,
            'parcel_number' => $parcel->parcelNumber,
            'area_m2' => $parcel->areaM2,
            'geom_ewkt' => $parcel->geomEwkt,
            'raw_gml' => json_encode($parcel->rawData, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE),
        ]);
    }
}