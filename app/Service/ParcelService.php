<?php

namespace App\Service;

use App\Entity\Parcel;

interface ParcelService {

    public function getParcelById(int $id): ?Parcel;

    public function getParcelByGlmId(string $gmlId): ?Parcel;

    public function getParcelByCadastralUnitCode(string $cadastralUnitCode): ?Parcel;

    public function getParcelByParcelNumber(string $parcelNumber): ?Parcel;

    public function upsertParcel(Parcel $parcel): void;

    public function getArea(float $minX, float $minY, float $maxX, float $maxY): array;

    public function deleteParcelByGlmId(string $gmlId): void;
}