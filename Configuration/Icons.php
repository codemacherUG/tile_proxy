<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider;

$extKey = 'tile_proxy';

return [
    // Icon identifier
    'tile-proxy' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:' . $extKey . '/Resources/Public/Icons/doktype-tileproxy.svg',
    ],
    'nominatim-proxy' => [
        'provider' => SvgIconProvider::class,
        'source' => 'EXT:' . $extKey . '/Resources/Public/Icons/doktype-nominatimproxy.svg',
    ],
];