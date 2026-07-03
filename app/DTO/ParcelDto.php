<?php

namespace App\DTO;

use App\Entity\Parcel;

class ParcelDto {

    function __construct(
        ?int    $id {
            get {
                return $this->id;
            }
            set {
                $this->id = $value;
            }
        },
        string  $gmlId {
            get {
                return $this->gmlId;
            }
            set {
                $this->gmlId = $value;
            }
        },
        ?string $nationalCadastralReference {
            get {
                return $this->nationalCadastralReference;
            }
            set {
                $this->nationalCadastralReference = $value;
            }
        },
        ?string $cadastralUnitCode {
            get {
                return $this->cadastralUnitCode;
            }
            set {
                $this->cadastralUnitCode = $value;
            }
        },
        ?string $cadastralUnitName {
            get {
                return $this->cadastralUnitName;
            }
            set {
                $this->cadastralUnitName = $value;
            }
        },
        ?string $parcelNumber {
            get {
                return $this->parcelNumber;
            }
            set {
                $this->parcelNumber = $value;
            }
        },
        ?float  $areaM2 {
            get {
                return $this->areaM2;
            }
            set {
                $this->areaM2 = $value;
            }
        },
        string  $geomEwkt {
            get {
                return $this->geomEwkt;
            }
            set {
                $this->geomEwkt = $value;
            }
        })
    { }

    public static function fromParcel(Parcel $parcel): self {
        return new self(
            $parcel->id,
            $parcel->gmlId,
            $parcel->nationalCadastralReference,
            $parcel->cadastralUnitCode,
            $parcel->cadastralUnitName,
            $parcel->parcelNumber,
            $parcel->areaM2,
            $parcel->geomEwkt
        );
    }
    
}