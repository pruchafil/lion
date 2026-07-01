<?php

declare(strict_types=1);

namespace Infrastructure\Cuzk;

use App\Entity\Parcel;
use DateTimeImmutable;
use SimpleXMLElement;

final readonly class ParcelXmlParser
{
    /**
     * @return Parcel[]
     * @throws \Exception
     */
    public function parse(string $xml): array
    {
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
                $geomEwkt,
                new DateTimeImmutable()
            );
        }

        return $parcels;
    }

    private function text(SimpleXMLElement $node, string $path): ?string
    {
        $result = $node->xpath($path);

        if ($result === false || count($result) === 0) {
            return null;
        }

        $value = trim((string) $result[0]);

        return $value !== '' ? $value : null;
    }

    private function posListToEwktPolygon(string $posList): string
    {
        $numbers = preg_split('/\s+/', trim($posList));

        if ($numbers === false || count($numbers) < 6 || count($numbers) % 2 !== 0) {
            throw new \RuntimeException('Invalid gml:posList.');
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