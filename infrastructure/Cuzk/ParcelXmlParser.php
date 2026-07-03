<?php

declare(strict_types=1);

namespace Infrastructure\Cuzk;

use App\Entity\Parcel;
use DateTimeImmutable;
use Exception;
use RuntimeException;
use SimpleXMLElement;

final readonly class ParcelXmlParser {
    /**
     * @return Parcel[]
     * @throws Exception
     */
    public function parseParcels(string $xml): array {
        $root = new SimpleXMLElement($xml);

        $root->registerXPathNamespace('wfs', 'http://www.opengis.net/wfs/2.0');
        $root->registerXPathNamespace('cp', 'http://inspire.ec.europa.eu/schemas/cp/4.0');
        $root->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');
        $root->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');

        $parcels = [];

        foreach ($root->xpath('//wfs:member/cp:CadastralParcel') as $node) {
            $node->registerXPathNamespace('cp', 'http://inspire.ec.europa.eu/schemas/cp/4.0');
            $node->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');
            $node->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');

            $gmlAttributes = $node->attributes('http://www.opengis.net/gml/3.2');
            $gmlId = (string) $gmlAttributes['id'];

            $area = $this->text($node, 'cp:areaValue');
            $label = $this->text($node, 'cp:label');
            $nationalReference = $this->text($node, 'cp:nationalCadastralReference');

            $zoning = $node->xpath('cp:zoning')[0] ?? null;
            $zoningAttributes = $zoning?->attributes('http://www.w3.org/1999/xlink');

            $kuName = $zoningAttributes ? (string) $zoningAttributes['title'] : null;
            $kuCode = null;

            if ($zoningAttributes && preg_match('/Id=CZ\.([0-9]+)/', (string) $zoningAttributes['href'], $m)) {
                $kuCode = $m[1];
            }

            $posList = $this->text($node, 'cp:geometry/gml:Polygon/gml:exterior/gml:LinearRing/gml:posList');

            if ($posList === null || $posList === '') {
                continue;
            }

            $geomEwkt = $this->posListToEwktPolygon($posList);

            $parcels[] = new Parcel(
                null,
                $gmlId,
                $nationalReference,
                $kuCode,
                $kuName,
                $label,
                $area !== null ? (float) $area : null,
                $geomEwkt
            );
        }

        return $parcels;
    }

    public function parseZoning(string $xml): array {
        $xmlElement = simplexml_load_string($xml);

        if ($xmlElement === false) {
            return [];
        }

        $xmlElement->registerXPathNamespace('wfs', 'http://www.opengis.net/wfs/2.0');
        $xmlElement->registerXPathNamespace('cp', 'http://inspire.ec.europa.eu/schemas/cp/4.0');
        $xmlElement->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');

        $zones = $xmlElement->xpath('//wfs:member/cp:CadastralZoning');

        if ($zones === false) {
            return [];
        }

        $result = [];

        foreach ($zones as $zone) {
            $zone->registerXPathNamespace('cp', 'http://inspire.ec.europa.eu/schemas/cp/4.0');
            $zone->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');

            $codeNode = $zone->xpath('cp:nationalCadastalZoningReference')[0] ?? null;
            $nameNode = $zone->xpath('cp:label')[0] ?? null;
            $posListNode = $zone->xpath('.//gml:posList')[0] ?? null;

            if ($codeNode === null || $nameNode === null || $posListNode === null) {
                continue;
            }

            [$minX, $minY, $maxX, $maxY] = $this->bboxFromPosList((string) $posListNode);

            $result[] = [
                'code' => (string) $codeNode,
                'name' => (string) $nameNode,
                'minX' => $minX,
                'minY' => $minY,
                'maxX' => $maxX,
                'maxY' => $maxY,
            ];
        }

        return $result;
    }

    private function bboxFromPosList(string $posList): array {
        $values = preg_split('/\s+/', trim($posList));

        $xs = [];
        $ys = [];

        for ($i = 0; $i < count($values); $i += 2) {
            $xs[] = (float) $values[$i];
            $ys[] = (float) $values[$i + 1];
        }

        return [
            min($xs),
            min($ys),
            max($xs),
            max($ys),
        ];
    }

    /**
     * @throws Exception
     */
    public function parseParcel(string $xml): Parcel {
        $root = new SimpleXMLElement($xml);

        $root->registerXPathNamespace('wfs', 'http://www.opengis.net/wfs/2.0');
        $root->registerXPathNamespace('cp', 'http://inspire.ec.europa.eu/schemas/cp/4.0');
        $root->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');
        $root->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');

        $parcelNodes = $root->xpath('//cp:CadastralParcel');
        $node = $parcelNodes[0] ?? $root;

        $node->registerXPathNamespace('cp', 'http://inspire.ec.europa.eu/schemas/cp/4.0');
        $node->registerXPathNamespace('gml', 'http://www.opengis.net/gml/3.2');
        $node->registerXPathNamespace('xlink', 'http://www.w3.org/1999/xlink');

        $gmlAttrs = $node->attributes('http://www.opengis.net/gml/3.2');
        $gmlId = (string) $gmlAttrs['id'];

        $area = $this->text($node, 'cp:areaValue');
        $label = $this->text($node, 'cp:label');
        $nationalReference = $this->text($node, 'cp:nationalCadastralReference');

        $zoning = $node->xpath('cp:zoning')[0] ?? null;
        $zoningAttrs = $zoning?->attributes('http://www.w3.org/1999/xlink');

        $kuName = $zoningAttrs ? (string) $zoningAttrs['title'] : null;
        $kuCode = null;

        if ($zoningAttrs && preg_match('/Id=CZ\.([0-9]+)/', (string) $zoningAttrs['href'], $m)) {
            $kuCode = $m[1];
        }

        $posList = $this->text(
            $node,
            './/cp:geometry//gml:Polygon/gml:exterior/gml:LinearRing/gml:posList'
        );

        if ($posList === null) {
            throw new RuntimeException('Parcel geometry not found. XML root: ' . $root->getName());
        }

        return new Parcel(
            null,
            $gmlId,
            $nationalReference,
            $kuCode,
            $kuName,
            $label,
            $area !== null ? (float) $area : null,
            $this->posListToEwktPolygon($posList)
        );
    }

    private function text(SimpleXMLElement $node, string $path): ?string {
        $result = $node->xpath($path);

        if ($result === false || count($result) === 0) {
            return null;
        }

        $value = trim((string) $result[0]);

        return $value !== '' ? $value : null;
    }

    private function posListToEwktPolygon(string $posList): string {
        $numbers = preg_split('/\s+/', trim($posList));

        if ($numbers === false || count($numbers) < 6 || count($numbers) % 2 !== 0) {
            throw new RuntimeException('Invalid gml:posList.');
        }

        $points = [];

        for ($i = 0; $i < count($numbers); $i += 2) {
            $points[] = $numbers[$i] . ' ' . $numbers[$i + 1];
        }

        if ($points[0] !== $points[count($points) - 1]) {
            $points[] = $points[0];
        }

        return 'SRID=5514;POLYGON((' . implode(',', $points) . '))';
    }
}