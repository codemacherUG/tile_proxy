<?php

namespace Codemacher\TileProxy;


class LatLngToTile 
{
  // https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames#PHP
  public static function lngToTileX($lng, $zoom)
  {
    return floor((($lng + 180) / 360) * pow(2, $zoom));
  }
  // https://wiki.openstreetmap.org/wiki/Slippy_map_tilenames#PHP
  public static function latToTileY($lat, $zoom)
  {
    return floor((1 - log(tan(self::degToRad($lat)) + 1 / cos(self::degToRad($lat))) / M_PI) / 2 * pow(2, $zoom));
  }

  public static function degToRad($deg)
  {
    return $deg * M_PI / 180;
  }

  public static function inRange($number, $min, $max)
  {
    return $number >= $min && $number <= $max;
  }
}