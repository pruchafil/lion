<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ParcelArea;
use App\Entity\Parcel;

interface ParcelRepository {
    public function getParcelById(int $id): ?Parcel;
    public function getParcelByGlmId(string $gmlId): ?Parcel;
    public function getParcelByCadastralUnitCode(string $cadastralUnitCode): ?Parcel;
    public function getParcelByParcelNumber(string $parcelNumber): ?Parcel;
    public function upsertParcel(Parcel $parcel): void;
    public function getArea(float $minX, float $minY, float $maxX, float $maxY): array;
}

