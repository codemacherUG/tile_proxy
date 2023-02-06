<?php

return [
    'frontend' => [
        'codemacher/tile_proxy/tileproxy' => [
            'target' => \Codemacher\TileProxy\Middleware\TileProxyMiddleware::class,
            'after' => [
                'typo3/cms-frontend/tsfe'
            ],
            'before' => [
                'typo3/cms-frontend/prepare-tsfe-rendering'
            ]
        ],
    ],
];