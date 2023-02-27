<?php
declare(strict_types=1);
namespace Codemacher\TileProxy\Utils;


class FolderUtil
{

  public static function calcSize(string $dir) : int
  {
    $size = 0;
    foreach (glob(rtrim($dir, '/') . '/*', GLOB_NOSORT) as $each) {
      $size += is_file($each) ? filesize($each) : FolderUtil::calcSize($each);
    }
    return $size;
  }
}