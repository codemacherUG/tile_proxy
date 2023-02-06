<?php

return [
    'frontend' => [
        'codemacher/tile_proxy/tileproxy' => [
            'target' => \Codemacher\TileProxy\Middleware\TileProxyMiddleware::class,
            'before' => [
                'typo3/cms-frontend/site'
            ]
        ],
    ],
];