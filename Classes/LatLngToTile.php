<?php

declare(strict_types=1);

namespace Codemacher\TileProxy;

class LatLngToTile
{
    // https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames#PHP
    public static function lngToTileX(float $lng, float $zoom): float
    {
        return floor((($lng + 180) / 360) * pow(2, $zoom));
    }
    // https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames#PHP
    public static function latToTileY(float $lat, int $zoom): float
    {
        return floor((1 - log(tan(self::degToRad($lat)) + 1 / cos(self::degToRad($lat))) / M_PI) / 2 * pow(2, $zoom));
    }

    public static function degToRad(float $deg): float
    {
        return $deg * M_PI / 180;
    }

    public static function inRange(float $number, float $min, float $max): bool
    {
        return $number >= $min && $number <= $max;
    }
}
