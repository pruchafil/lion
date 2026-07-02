<?php

namespace App\Entity;

use DateMalformedStringException;

final class ParcelArea {
    public function __construct(
        ?int $id {
            get {
                return $this->id;
            }
            set {
                $this->id = $value;
            }
        },
        string $geomEwkt {
            get {
                return $this->geomEwkt;
            }
            set {
                $this->geomEwkt = $value;
            }
        }
    ) { }

    public static function fromDatabaseRow(array $row): self {
        return new self(
            (isset($row['id']) ? (int) $row['id'] : null),
            $row['geom_ewkt']
        );
    }
}