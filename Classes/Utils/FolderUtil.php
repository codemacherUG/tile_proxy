<?php

declare(strict_types=1);

namespace Codemacher\TileProxy\Utils;

class FolderUtil
{
    public static function formatFilesize(int $bytes): string
    {
        if ($bytes == 0) {
            return "0.00 B";
        }
        $s = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
        $e = floor(log($bytes, 1024));
        return round($bytes / pow(1024, $e), 2) . $s[$e];
    }

    public static function getFolderInfo(string $dir): array
    {
        $size = 0;
        $files = 0;
        $list = glob(rtrim($dir, '/') . '/*', GLOB_NOSORT);
        if($list) {
            foreach ($list as $entry) {
                if (is_file($entry)) {
                    $size += filesize($entry);
                    ++$files;
                } else {
                    $info = FolderUtil::getFolderInfo($entry);
                    $files += $info["files"];
                    $size += $info["size"];
                }
            }
        }
        return [
          "size" => $size,
          "files" => $files
        ];
    }
}
