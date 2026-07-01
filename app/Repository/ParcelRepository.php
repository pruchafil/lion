<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Parcel;
use DateMalformedStringException;
use DateTimeImmutable;
use JsonException;
use PDO;

interface ParcelRepository {
    public function getParcelById(int $id): ?Parcel;
    public function getParcelByGlmId(string $gmlId): ?Parcel;
    public function getParcelByCadastralUnitCode(string $cadastralUnitCode): ?Parcel;
    public function getParcelByParcelNumber(string $parcelNumber): ?Parcel;
    public function upsertParcel(Parcel $parcel): void;
}

