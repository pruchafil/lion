<?php

namespace App\Service;

use App\Entity\Parcel;
use App\Repository\CachedRepository;
use App\Repository\ParcelRepository;
use DateTime;

final class ParcelServiceImpl implements ParcelService {
    private DateTime $lastGlobalSync;

    function __construct(
        private readonly ParcelRepository $parcelRepository,
        private readonly ParcelSyncService $parcelSyncService,
        private readonly CachedRepository $cachedRepository
    )
    { }

    private function syncParcels(): void {
        $now = new DateTime();
        if ($now->diff($this->cachedRepository->get())->days >= 30) {
            $this->parcelSyncService->updateGlobalCache();

            $this->cachedRepository->update($now);
        }
    }

    public function getParcelById(int $id): ?Parcel {
        $this->syncParcels();
        return $this->parcelRepository->getParcelById($id);
    }

    public function getParcelByGlmId(string $gmlId): ?Parcel {
        $this->syncParcels();
        return $this->parcelRepository->getParcelByGlmId($gmlId);
    }

    public function getParcelByCadastralUnitCode(string $cadastralUnitCode): ?Parcel {
        $this->syncParcels();
        return $this->parcelRepository->getParcelByCadastralUnitCode($cadastralUnitCode);
    }

    public function getParcelByParcelNumber(string $parcelNumber): ?Parcel {
        $this->syncParcels();
        return $this->parcelRepository->getParcelByParcelNumber($parcelNumber);
    }

    public function upsertParcel(Parcel $parcel): void {
        $this->syncParcels();
        $this->parcelRepository->upsertParcel($parcel);
    }

    public function getArea(float $minX, float $minY, float $maxX, float $maxY): array {
        $this->syncParcels();
        return $this->parcelRepository->getArea($minX, $minY, $maxX, $maxY);
    }

    public function deleteParcelByGlmId(string $gmlId): void {
        $this->syncParcels();
        $this->parcelRepository->deleteParcelByGlmId($gmlId);
    }
}