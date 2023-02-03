<?php

return [
    'frontend' => [
        'codemacher/tile_proxy/tileproxy' => [
            'target' => \Codemacher\TileProxy\Middleware\TileProxyMiddleware::class,
            'after' => [
                'typo3/cms-core/normalized-params-attribute'
            ]
        ],
    ],
];