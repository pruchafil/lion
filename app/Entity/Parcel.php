<?php

declare(strict_types=1);

namespace App\Entity;

use DateMalformedStringException;
use DateTimeImmutable;
use JsonException;

final class Parcel
{
    public function __construct(
        ?int               $id {
            get {
                return $this->id;
            }
            set {
                $this->id = $value;
            }
        },
        string             $gmlId {
            get {
                return $this->gmlId;
            }
            set {
                $this->gmlId = $value;
            }
        },
        ?string            $nationalCadastralReference {
            get {
                return $this->nationalCadastralReference;
            }
            set {
                $this->nationalCadastralReference = $value;
            }
        },
        ?string            $cadastralUnitCode {
            get {
                return $this->cadastralUnitCode;
            }
            set {
                $this->cadastralUnitCode = $value;
            }
        },
        ?string            $cadastralUnitName {
            get {
                return $this->cadastralUnitName;
            }
            set {
                $this->cadastralUnitName = $value;
            }
        },
        ?string            $parcelNumber {
            get {
                return $this->parcelNumber;
            }
            set {
                $this->parcelNumber = $value;
            }
        },
        ?float             $areaM2 {
            get {
                return $this->areaM2;
            }
            set {
                $this->areaM2 = $value;
            }
        },
        string             $geomEwkt {
            get {
                return $this->geomEwkt;
            }
            set {
                $this->geomEwkt = $value;
            }
        },
        \DateTimeImmutable $cachedAt {
            get {
                return $this->cachedAt;
            }
            set {
                $this->cachedAt = $value;
            }
        }
    ) { }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'gmlId' => $this->gmlId,
            'nationalCadastralReference' => $this->nationalCadastralReference,
            'cadastralUnitCode' => $this->cadastralUnitCode,
            'cadastralUnitName' => $this->cadastralUnitName,
            'parcelNumber' => $this->parcelNumber,
            'areaM2' => $this->areaM2,
            'cachedAt' => $this->cachedAt->format(DATE_ATOM)
        ];
    }

    /**
     * @throws DateMalformedStringException
     */
    public static function fromDatabaseRow(array $row): self {
        return new self(
            (isset($row['id']) ? (int) $row['id'] : null),
            $row['cuzk_gml_id'],
                $row['national_cadastral_reference'] ?? null,
                $row['ku_code'] ?? null,
                $row['ku_name'] ?? null,
                $row['parcel_number'] ?? null,
                $row['area_m2'] != null ? (float)$row['area_m2'] : null,
            $row['geom_ewkt'],
            new DateTimeImmutable($row['cached_at'])
        );
    }
}